<?php

declare(strict_types=1);

/*
 * This file is developed by evoWeb.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Evoweb\StoreFinder\Services;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\DocParser;
use Evoweb\StoreFinder\Annotation\Validate;
use Evoweb\StoreFinder\Validation\Validator\ConstraintValidator;
use Evoweb\StoreFinder\Validation\Validator\SetPropertyNameInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use ReflectionException;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Attribute as Extbase;
use TYPO3\CMS\Extbase\Mvc\Controller\Argument;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Validation\Validator\ConjunctionValidator;
use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;
use TYPO3\CMS\Extbase\Validation\ValidatorResolver;

readonly class ModifyValidator
{
    protected LoggerInterface $logger;

    public function __construct(
        protected ValidatorResolver $validatorResolver,
        LogManager $logManager,
    ) {
        $this->logger = $logManager->getLogger(__CLASS__);
    }

    /**
     * @param array<string, mixed> $settings
     */
    public function shouldValidationBeModified(
        Arguments $arguments,
        array $settings,
    ): bool {
        $validation = $settings['validation'] ?? [];
        return (
            $arguments->hasArgument('constraint')
            && is_array($validation)
            && !empty($validation)
        );
    }

    /**
     * @param array<string, mixed> $settings
     */
    public function modifyArgumentValidators(
        Arguments $arguments,
        RequestInterface $request,
        array $settings,
    ): Arguments {
        foreach ($arguments as $argumentName => $argument) {
            if ($argumentName !== 'constraint') {
                continue;
            }

            $this->modifyValidatorsBasedOnSettings(
                $argument,
                $request,
                $settings['validation'] ?? [],
            );
        }
        return $arguments;
    }

    /**
     * @param array<string, string|string[]> $configuredValidators
     */
    public function modifyValidatorsBasedOnSettings(
        Argument $argument,
        ServerRequestInterface $request,
        array $configuredValidators,
    ): void {
        $parser = new DocParser();

        /** @var ConstraintValidator $validator */
        $validator = GeneralUtility::makeInstance(ConstraintValidator::class);
        foreach ($configuredValidators as $fieldName => $configuredValidator) {
            if (!is_array($configuredValidator)) {
                try {
                    $validatorInstance = $this->getValidatorByConfiguration(
                        $configuredValidator,
                        $parser,
                        $fieldName,
                        $request,
                    );
                } catch (\Exception $exception) {
                    $this->logger->debug($exception->getMessage());
                    continue;
                }
            } else {
                /** @var ConjunctionValidator $validatorInstance */
                $validatorInstance = $this->validatorResolver->createValidator(
                    ConjunctionValidator::class
                );
                foreach ($configuredValidator as $individualConfiguredValidator) {
                    try {
                        $individualValidatorInstance = $this->getValidatorByConfiguration(
                            $individualConfiguredValidator,
                            $parser,
                            $fieldName,
                            $request,
                        );
                    } catch (\Exception $exception) {
                        $this->logger->debug($exception->getMessage());
                        continue;
                    }

                    $validatorInstance->addValidator($individualValidatorInstance);
                }
            }

            $validator->addPropertyValidator($fieldName, $validatorInstance);
        }

        $argument->setValidator($validator);
    }

    /**
     * @throws ReflectionException
     * @throws AnnotationException
     */
    protected function getValidatorByConfiguration(
        string $configuration,
        DocParser $parser,
        string $fieldName,
        ServerRequestInterface $request
    ): ?ValidatorInterface {
        if (!str_contains($configuration, '"') && !str_contains($configuration, '(')) {
            $configuration = '"' . $configuration . '"';
        }

        /** @var Extbase\Validate $validateAnnotation */
        $configuration = '@' . Validate::class . '(' . $configuration . ')';
        $validateAnnotation = current($parser->parse($configuration));
        $validator = $this->validatorResolver->createValidator(
            $validateAnnotation->validator,
            $validateAnnotation->options,
            $request
        );

        if ($validator instanceof SetPropertyNameInterface) {
            $validator->setPropertyName($fieldName);
        }

        return $validator;
    }
}
