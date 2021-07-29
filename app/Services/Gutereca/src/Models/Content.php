<?php

namespace VanOns\Gutereca\Models;

use Illuminate\Database\Eloquent\Model;
use mysql_xdevapi\Exception;
use VanOns\Gutereca\Helpers\EmbedHelper;
use VanOns\Gutereca\Helpers\BlockHelper;
use VanOns\Gutereca\Events\ContentCreated;
use VanOns\Gutereca\Events\ContentUpdated;
use VanOns\Gutereca\Events\ContentRendered;

class Content extends Model
{

    protected $table = 'lb_contents';

    public static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            event(new ContentCreated($model));
        });

        static::updated(function ($model) {
            event(new ContentUpdated($model));
        });
    }

    public function contentable()
    {
        return $this->morphTo();
    }

    /**
     * Returns the rendered content of the content
     * @return String - The completely rendered content
     */
    public function render()
    {
        $html = BlockHelper::renderBlocks($this->rendered_content);

        event(new ContentRendered($this));

        return "<div class='gutenberg__content wp-embed-responsive'>$html</div>";
    }

    /**
     * Sets the raw content and performs some initial rendering
     * @param String $html
     */
    public function setContent($html)
    {
        $this->raw_content = $this->fixEmptyImages($html);
        $this->renderRaw();
    }

    /**
     * Renders the HTML of the content object
     */
    public function renderRaw()
    {
        $this->rendered_content = EmbedHelper::renderEmbeds($this->raw_content);

        event(new ContentRendered($this));

        return $this->rendered_content;
    }

    /**
     * TODO: Remove this temporary fix for Image block crashing when no image is selected
     */
    private function fixEmptyImages($html) {
        $regex = '/<img(.*)\/>/';
        return preg_replace_callback($regex, function ($matches) {
            if (isset($matches[1]) && strpos($matches[1], 'src="') === false) {
                return str_replace('<img ', '<img src="/vendor/gutereca/img/placeholder.jpg" ', $matches[0]);
            }
            return $matches[0];
        }, $html);
    }
}
