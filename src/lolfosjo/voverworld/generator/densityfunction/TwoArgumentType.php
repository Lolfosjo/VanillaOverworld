<?php

declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

enum TwoArgumentType {
    case ADD;
    case MUL;
    case MIN;
    case MAX;
}