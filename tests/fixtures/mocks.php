<?php

declare(strict_types=1);

namespace Platine\Framework\Handler\Error\Renderer;

$mock_htmlentities_to_empty = false;

function htmlentities(string $key)
{
    global $mock_htmlentities_to_empty;
    if ($mock_htmlentities_to_empty) {
        return '';
    }

    return \htmlentities($key);
}
