<?php

namespace SergiX44\Nutgram\Telegram\Types\Media;

use SergiX44\Nutgram\Telegram\Types\BaseType;

/**
 * This object represents a dice with a random value from 1 to 6 for currently supported base emoji.
 * (Yes, we're aware of the βproperβ singular of die.
 * But it's awkward, and we decided to help it change. One dice at a time!)
 * @see https://core.telegram.org/bots/api#dice
 */
class Dice extends BaseType
{
    /**
     * Emoji on which the dice throw animation is based
     */
    public string $emoji;

    /**
     * Value of the dice, 1-6 for βπ²β and βπ―β base emoji, 1-5 for βπβ base emoji
     */
    public int $value;
}
