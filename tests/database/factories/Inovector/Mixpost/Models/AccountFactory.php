<?php

namespace Database\Factories\Inovector\Mixpost\Models;

use Inovector\Mixpost\Database\Factories\AccountFactory as MixpostAccountFactory;

/**
 * Wrapper factory for Account model to satisfy Laravel's factory discovery.
 *
 * Laravel expects factories in Database\Factories\{ModelNamespace}\{ModelName}Factory
 * but Mixpost provides them in Inovector\Mixpost\Database\Factories\{ModelName}Factory
 *
 * This wrapper extends the actual Mixpost factory, inheriting all its logic.
 */
class AccountFactory extends MixpostAccountFactory
{
    // Inherits all behavior from Mixpost's AccountFactory
}
