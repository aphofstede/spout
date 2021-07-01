<?php

namespace Box\Spout\Common\Entity\Style;

/**
 * Class Vertical Alignment
 * This class provides constants to work with text vertical alignment.
 */
abstract class CellVerticalAlignment
{
    const TOP = 'top';
    const CENTER = 'center';
    const BOTTOM = 'bottom';
    const JUSTIFY = 'justify';
    const DISTRIBUTED = 'distributed';

    private static $VALID_VERTICAL_ALIGNMENTS = [
        self::TOP => 1,
        self::CENTER => 1,
        self::BOTTOM => 1,
        self::JUSTIFY => 1,
        self::DISTRIBUTED => 1,
    ];

    /**
     * @param string $cellVerticalAlignment
     *
     * @return bool Whether the given cell vertical alignment is valid
     */
    public static function isValid($cellVerticalAlignment)
    {
        return isset(self::$VALID_VERTICAL_ALIGNMENTS[$cellVerticalAlignment]);
    }
}
