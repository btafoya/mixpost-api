<?php

namespace Database\Factories\Inovector\Mixpost\Models;

use Inovector\Mixpost\Database\Factories\MediaFactory as MixpostMediaFactory;

/**
 * Wrapper factory for Media model to satisfy Laravel's factory discovery.
 *
 * Laravel expects factories in Database\Factories\{ModelNamespace}\{ModelName}Factory
 * but Mixpost provides them in Inovector\Mixpost\Database\Factories\{ModelName}Factory
 *
 * This wrapper extends the actual Mixpost factory, inheriting all its logic.
 */
class MediaFactory extends MixpostMediaFactory
{
    // Inherits all behavior from Mixpost's MediaFactory
}
