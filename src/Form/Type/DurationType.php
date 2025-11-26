<?php

namespace Kachnitel\DurationBundle\Form\Type;

use Kachnitel\DurationBundle\Service\DurationUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form type for duration input fields.
 *
 * This form type handles duration values stored as integers (seconds) in your entities
 * while presenting them to users in human-readable format (e.g., "2h 30m").
 *
 * Usage in forms:
 *   $builder->add('duration', DurationType::class, [
 *       'label' => 'Task Duration',
 *       'help' => 'Examples: 2h 30m, 90 minutes, 02:30'
 *   ]);
 *
 * Supported input formats:
 * - "2h 30m" or "2h30m"
 * - "2.5 hours"
 * - "90 minutes" or "90m"
 * - "HH:MM" (e.g., "02:30")
 * - "HH:MM:SS" (e.g., "02:30:45")
 * - Plain seconds (e.g., "9000")
 */
class DurationType extends AbstractType implements DataTransformerInterface
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer($this);
    }

    public function getParent(): string
    {
        return TextType::class;
    }

    /**
     * Transform seconds (from entity) to human-readable string (for display).
     *
     * @param int|null $seconds Duration in seconds
     * @return string|null Formatted duration string
     */
    public function transform($seconds): ?string
    {
        if ($seconds === null) {
            return null;
        }

        return DurationUtil::toString($seconds);
    }

    /**
     * Transform human-readable string (from form input) to seconds (for entity).
     *
     * @param string|null $interval Duration string
     * @return int Duration in seconds
     */
    public function reverseTransform($interval): int
    {
        if ($interval === null) {
            return 0;
        }

        return DurationUtil::toSeconds($interval);
    }
}
