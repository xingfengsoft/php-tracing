<?php

namespace OpenTracing\Exceptions;

use DomainException;

/**
 * Thrown when a reference has more than one parent in the SpanOptions
 */
final class InvalidReferencesSet extends DomainException
{
    public static function create($message)
    {
        return new self($message);
    }

    public static function forMoreThanOneParent()
    {
        return new self('Span can not have more than one parent, either one as child_of or either one as follows_from');
    }
}
