<?php

namespace Wexample\SymfonyConversation\Traits;

use Wexample\SymfonyConversation\WexampleSymfonyConversationBundle;
use Wexample\SymfonyHelpers\Traits\BundleClassTrait;

trait SymfonyConversationBundleClassTrait
{
    use BundleClassTrait;

    public static function getBundleClassName(): string
    {
        return WexampleSymfonyConversationBundle::class;
    }
}
