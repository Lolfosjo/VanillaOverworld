<?php
declare(strict_types=1);

namespace lolfosjo\voverworld\generator\densityfunction;

enum MappedType {
    case ABS;
    case SQUARE;
    case CUBE;
    case HALF_NEGATIVE;
    case QUARTER_NEGATIVE;
    case INVERT;
    case SQUEEZE;
}