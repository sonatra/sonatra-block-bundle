<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Block\Extension\Core\DataTransformer;

use Sonatra\Bundle\BlockBundle\Block\DataTransformerInterface;
use Sonatra\Bundle\BlockBundle\Block\Exception\TransformationFailedException;
use Sonatra\Bundle\BlockBundle\Block\Exception\UnexpectedTypeException;

/**
 * Transforms between a normalized time and a localized time string
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class DateTimeToLocalizedStringTransformer implements DataTransformerInterface
{
    protected $calendar;
    protected $dateFormat;
    protected $timeFormat;
    protected $timezone;
    protected $locale;
    protected static $formats = array(
        \IntlDateFormatter::NONE,
        \IntlDateFormatter::FULL,
        \IntlDateFormatter::LONG,
        \IntlDateFormatter::MEDIUM,
        \IntlDateFormatter::SHORT,
    );

    /**
     * Constructor.
     *
     * @param int    $calendar
     * @param int    $dateFormat
     * @param int    $timeFormat
     * @param int    $timezone
     * @param string $locale
     *
     * @throws UnexpectedTypeException When the date format is not valid
     * @throws UnexpectedTypeException When the time format is not valid
     */
    public function __construct($calendar = null, $dateFormat = null, $timeFormat = null, $timezone = null, $locale = null)
    {
        if (null === $calendar) {
            $calendar = \IntlDateFormatter::GREGORIAN;
        }

        if (null === $dateFormat) {
            $dateFormat = \IntlDateFormatter::SHORT;
        }

        if (null === $timeFormat) {
            $timeFormat = \IntlDateFormatter::SHORT;
        }

        if (null === $locale) {
            $locale = \Locale::getDefault();
        }

        if (!in_array($dateFormat, self::$formats, true)) {
            throw new UnexpectedTypeException($dateFormat, implode('", "', self::$formats));
        }

        if (!in_array($timeFormat, self::$formats, true)) {
            throw new UnexpectedTypeException($timeFormat, implode('", "', self::$formats));
        }

        $this->calendar = $calendar;
        $this->dateFormat = $dateFormat;
        $this->timeFormat = $timeFormat;
        $this->timezone = $timezone;
        $this->locale = $locale;
    }

    /**
     * Transforms a normalized date into a localized date string/array.
     *
     * @param \DateTime $dateTime Normalized date.
     *
     * @return string|null Localized date string.
     *
     * @throws TransformationFailedException if the given value is not an instance of \DateTime
     */
    public function transform($dateTime)
    {
        if (null === $dateTime) {
            return '';
        }

        if (!$dateTime instanceof \DateTime) {
            throw new TransformationFailedException('Expected a \DateTime.');
        }

        // convert time to UTC before passing it to the formatter
        $dateTime = clone $dateTime;
        $value = $this->getIntlDateFormatter()->format((int) $dateTime->format('U'));

        return $value;
    }

    /**
     * Returns a preconfigured IntlDateFormatter instance
     *
     * @return \IntlDateFormatter
     */
    protected function getIntlDateFormatter()
    {
        $intlDateFormatter = new \IntlDateFormatter(
                $this->locale,
                $this->dateFormat,
                $this->timeFormat,
                $this->timezone,
                $this->calendar,
                null
        );
        $intlDateFormatter->setLenient(false);

        return $intlDateFormatter;
    }
}
