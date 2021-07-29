<?php

namespace VanOns\Gutereca\Models;

use VanOns\Gutereca\Models\Content;
use VanOns\Gutereca\Events\ContentCreated;
use VanOns\Gutereca\Events\ContentUpdated;

trait Gutenbergable
{
    /**
     * Delete content when model gets deleted
     */
    protected static function bootGutenbergable()
    {
        // Persisting gutereca contents only when the current model has been updated
        self::saved(function ($model) {
            if ($content = $model->guterecaContent) {
                $content->contentable()
                        ->associate($model)->save();
            }
        });

        // Permanently deleting laravel content when this model has been deleted
        self::deleted(function ($model) {
            $model->guterecaContent()->delete();
        });
    }

    /**
     * Relationship to the lb_contents table.
     *
     * @return mixed
     */
    public function guterecaContent()
    {
        return $this->morphOne(Content::class, 'contentable');
    }

    /**
     * Get the rendered content.
     *
     * @return string
     */
    public function getLbContentAttribute()
    {
        return $this->guterecaContent ? $this->guterecaContent->render() : '';
    }

    /**
     * Set the gutereca content.
     *
     * @param $content
     */
    public function setLbContentAttribute($content)
    {
        if (! $this->guterecaContent) {
            $this->setRelation('guterecaContent', new Content);
        }

        $this->guterecaContent->setContent($content);
    }

    /**
     * Get the raw gutereca content.
     */
    public function getLbRawContentAttribute()
    {
        if (! $this->guterecaContent) {
            return '';
        };

        return $this->guterecaContent->raw_content;
    }

    /**
     * Returns the raw content that came out of Gutenberg
     *
     * @return String
     * @deprecated
     */
    public function getRawContent()
    {
        return $this->getLbRawContentAttribute();
    }

    /**
     * Returns the Gutenberg content with some initial rendering done to it
     *
     * @return String
     * @deprecated
     */
    public function getRenderedContent()
    {
        return $this->guterecaContent->rendered_content;
    }

    /**
     * Sets the content object using the raw editor content
     *
     * @param String $content
     * @param String $save - Calls .save() on the Content object if true
     * @deprecated
     */
    public function setContent($content, $save = false)
    {
        if (! $this->guterecaContent) {
            $this->createContent();
        }

        $this->guterecaContent->setContent($content);
        if ($save) {
            $this->guterecaContent->save();
        }
        event(new ContentUpdated($this->guterecaContent));
    }
}
