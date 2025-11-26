# DurationBundle

A Symfony bundle for handling duration parsing, formatting, and form integration. Parse human-readable duration strings (e.g., "2h 30m", "HH:MM:SS") and format seconds into readable formats.

## Features

- **Parse multiple duration formats**: "2h 30m", "2.5 hours", "90 minutes", "HH:MM", "HH:MM:SS"
- **Format durations**: Convert seconds to human-readable strings with customizable units
- **Symfony Form integration**: DurationType for seamless form handling
- **Twig filters**: Display durations in templates with ease
- **Flexible unit configuration**: Support for years, months, weeks, days, hours, minutes, seconds
- **Production-tested**: Extracted from real-world application code

## Installation

Install the bundle via Composer:

```bash
composer require kachnitel/duration-bundle
```

If you're not using Symfony Flex, enable the bundle in `config/bundles.php`:

```php
return [
    // ...
    Kachnitel\DurationBundle\DurationBundle::class => ['all' => true],
];
```

## Usage

### 1. Service Usage

The `DurationUtil` service provides static methods for duration manipulation:

```php
use Kachnitel\DurationBundle\Service\DurationUtil;

// Parse duration strings to seconds
$seconds = DurationUtil::toSeconds('2h 30m');        // 9000
$seconds = DurationUtil::toSeconds('2.5 hours');     // 9000
$seconds = DurationUtil::toSeconds('90 minutes');    // 5400
$seconds = DurationUtil::toSeconds('02:30');         // 9000
$seconds = DurationUtil::toSeconds('02:30:45');      // 9045

// Format seconds to human-readable strings
$duration = DurationUtil::toString(9000);                    // "2h 30m"
$duration = DurationUtil::toString(9000, false);             // "2 hours 30 minutes"
$duration = DurationUtil::toString(9000, true, ['h', 'm']); // "2h 30m"

// Format as HH:MM
$time = DurationUtil::toHhMm(9000);  // "02:30"

// Convert between DateInterval and seconds
$interval = new DateInterval('PT2H30M');
$seconds = DurationUtil::intervalToSeconds($interval);  // 9000

$interval = DurationUtil::secondsToInterval(9000);  // DateInterval object
```

### 2. Form Integration

Use `DurationType` in your forms to handle duration input:

```php
use Kachnitel\DurationBundle\Form\Type\DurationType;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('duration', DurationType::class, [
                'label' => 'Task Duration',
                'help' => 'Examples: 2h 30m, 90 minutes, 02:30',
                'required' => false,
            ]);
    }
}
```

In your entity, store the duration as an integer (seconds):

```php
class Task
{
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $duration = null;

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): self
    {
        $this->duration = $duration;
        return $this;
    }
}
```

The form type automatically:
- Converts seconds to human-readable format for display (e.g., "2h 30m")
- Parses user input back to seconds for storage

**Supported Input Formats:**
- `2h 30m` or `2h30m` - Hours and minutes with short units
- `2.5 hours` - Decimal with long unit names
- `90 minutes` or `90m` - Minutes only
- `02:30` - HH:MM format (interpreted as hours:minutes)
- `02:30:45` - HH:MM:SS format
- `9000` - Plain seconds

### 3. Twig Filters

Display durations in your templates using the provided Twig filters:

```twig
{# Format duration in short format #}
{{ task.duration|duration }}
{# Output: "2h 30m" #}

{# Format duration in long format #}
{{ task.duration|duration(false) }}
{# Output: "2 hours 30 minutes" #}

{# Format as HH:MM #}
{{ task.duration|hhmm }}
{# Output: "02:30" #}

{# Parse duration string to seconds #}
{{ "2h 30m"|toSeconds }}
{# Output: 9000 #}
```

## Advanced Configuration

### Custom Unit Selection

You can specify which units to include when formatting:

```php
use Kachnitel\DurationBundle\Service\DurationUtil;

// Only hours and minutes
$duration = DurationUtil::toString(93784, true, ['h', 'm']);  // "26h 3m"

// Days, hours, and minutes
$duration = DurationUtil::toString(93784, true, ['d', 'h', 'm']);  // "1d 2h 3m"

// All units
$duration = DurationUtil::toString(93784, true, ['y', 'mo', 'w', 'd', 'h', 'm', 's']);
// Output depends on the value
```

### Available Units

The bundle supports the following units (defined in `DurationUtil::UNIT_CONFIG`):

| Unit | Short | Long | Seconds |
|------|-------|------|---------|
| `y` | y | year/years | 31,536,000 |
| `mo` | mo | month/months | 2,592,000 |
| `w` | w | week/weeks | 604,800 |
| `d` | d | day/days | 86,400 |
| `h` | h | hour/hours | 3,600 |
| `m` | m | minute/minutes | 60 |
| `s` | s | second/seconds | 1 |

## Examples

### Example 1: Time Tracking Application

```php
// Entity
class TimeEntry
{
    #[ORM\Column(type: 'integer')]
    private int $duration = 0;
}

// Form
$builder->add('duration', DurationType::class, [
    'label' => 'Duration',
    'help' => 'Enter duration (e.g., 2h 30m)',
]);

// Template
<div class="time-entry">
    <strong>Duration:</strong> {{ entry.duration|duration }}
</div>
```

### Example 2: Task Estimation

```twig
{% if task.estimatedDuration %}
    <span class="badge">
        Estimated: {{ task.estimatedDuration|hhmm }}
    </span>
{% endif %}

{% if task.actualDuration %}
    <span class="badge {% if task.actualDuration > task.estimatedDuration %}text-danger{% endif %}">
        Actual: {{ task.actualDuration|hhmm }}
    </span>
{% endif %}
```

### Example 3: Service Layer

```php
use Kachnitel\DurationBundle\Service\DurationUtil;

class ReportGenerator
{
    public function generateTimeReport(array $tasks): array
    {
        $totalSeconds = array_sum(array_column($tasks, 'duration'));

        return [
            'total_time' => DurationUtil::toString($totalSeconds, false),
            'total_hours' => DurationUtil::toHhMm($totalSeconds),
            'task_count' => count($tasks),
            'average_time' => DurationUtil::toString($totalSeconds / count($tasks)),
        ];
    }
}
```

## Requirements

- PHP 8.2 or higher
- Symfony 6.4 or 7.0+

## Testing

Run the test suite:

```bash
composer install
vendor/bin/phpunit
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This bundle is released under the MIT License. See the [LICENSE](LICENSE) file for details.

## Credits

Developed by [Mr. Duck](https://github.com/kachnitel)

Extracted from production code and open-sourced for the Symfony community.

## Support

If you encounter any issues or have questions:

- Open an issue on [GitHub](https://github.com/kachnitel/duration-bundle/issues)
- Check existing issues for solutions

## Changelog

### 1.0.0 (Initial Release)

- Duration parsing from multiple formats
- Duration formatting with customizable units
- Symfony Form integration (DurationType)
- Twig filters (duration, toSeconds, hhmm)
- DateInterval conversion utilities
- Full test coverage
