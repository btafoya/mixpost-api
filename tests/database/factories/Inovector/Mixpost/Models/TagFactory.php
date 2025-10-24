<?php

namespace Database\Factories\Inovector\Mixpost\Models;

use Inovector\Mixpost\Database\Factories\TagFactory as MixpostTagFactory;

/**
 * Wrapper factory for Tag model to satisfy Laravel's factory discovery.
 *
 * Laravel expects factories in Database\Factories\{ModelNamespace}\{ModelName}Factory
 * but Mixpost provides them in Inovector\Mixpost\Database\Factories\{ModelName}Factory
 *
 * This wrapper extends the actual Mixpost factory, inheriting all its logic.
 */
class TagFactory extends MixpostTagFactory
{
    // Inherits all behavior from Mixpost's TagFactory
}
