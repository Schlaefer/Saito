<?php

/**
 * Add any string here that may be dynamicaly created and not verbatim in the
 * source.
 *
 * In source
 *    $a = bar;
 *    $a = baz;
 *    …
 *    __('foo-'.$a);
 *
 * becomes
 *
 *    __('foo-bar');
 *    __('foo_baz');
 *
 * here.
 */
