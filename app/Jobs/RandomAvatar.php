<?php

namespace Pixie\Jobs;

use Intervention\Image\AbstractFont;

/**
 * Class RandomAvatar.
 *
 * Random text avatar generator.
 */
class RandomAvatar extends Job
{
    /**
     * Generates a random HEX (HTML) color which is not completely black, while keeping
     * a bigger distance from white because of the white initials.
     *
     * @return string HEX formatted color code.
     */
    protected function randomColor()
    {
        // generate a red point.
        $red = dechex(rand(16, 196));
        // generate a green point.
        $green = dechex(rand(16, 196));
        // generate a blue point.
        $blue = dechex(rand(16, 196));

        // concat and return the color (with the hash prefix)
        return "#{$red}{$green}{$blue}";
    }

    /**
     * Generate the initials to set on the random image.
     *
     * @return string
     */
    protected function getInitials()
    {
        // replace dots and underscores with dashes.
        $dashedUsername = str_replace(['.', '_'], '-', $this->username);

        // ensure no special character was left...
        $normalized = str_slug($dashedUsername);

        // when the username has no dashes afer normalization...
        if (!str_contains($normalized, '-')) {
            // return the first two characters (UPPERCASE).
            return mb_strtoupper(mb_substr($normalized, 0, 2));
        }

        // when there is a dash, the we can explode into pieces.
        $parts = explode('-', $normalized);

        // get the initial A from the first string part.
        $initialA = mb_substr($parts[0], 0, 1);
        // get the initial B from the second string part.
        $initialB = mb_substr($parts[1], 0, 1);

        // concatenate the both characters and return (UPPERCASE).
        return mb_strtoupper("{$initialA}{$initialB}");
    }

    /**
     * Job handler (random avatar generator).
     *
     * @return mixed
     */
    public function handle()
    {
        // start an "empty canvas", pun intended.
        $canvas = $this->imageManager->canvas($this->baseSize, $this->baseSize);

        // spin the wheel of fortune and retrieve a color.
        $color = $this->randomColor();

        // taint that canvas with the random color.
        $canvas->fill($color);

        // determine the middle of the canvas.
        $halfSize = round(($this->baseSize / 2));

        // quarter size is the text boundary.
        $quarterSize = round(($halfSize / 2));

        // get the initials from the username.
        $initials = $this->getInitials();

        // get the text settings functions.
        $textSettings = $this->getTextSettingsFunction();

        // write the initials on the canvas.
        $canvas->text($initials, $quarterSize, $quarterSize, $textSettings);

        // return the generated canvas.
        return $canvas;
    }

    /**
     * Factory a text configuration function for the Image package.
     *
     * @return \Closure
     */
    protected function getTextSettingsFunction() : \Closure
    {
        // create a text settings function.
        $textSettings = function (AbstractFont $font) {
            // determine the middle of the canvas.
            $halfSize = round(($this->baseSize / 2));
            // load the custom font.
            $font->file(storage_path($this->customFont));
            // set the initials boundaries.
            $font->size($halfSize);
            // set the text white.
            $font->color('#FFFFFF');
            // center and...
            $font->align('left');
            // ... middle the text.
            $font->valign('top');
        };

        // return the text settings function.
        return $textSettings;
    }
}
