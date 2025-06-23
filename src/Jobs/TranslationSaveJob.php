<?php

namespace Barryvdh\TranslationManager\Jobs;

use Barryvdh\TranslationManager\Models\Translation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class TranslationJob
 *
 * @author MimoGraphix <mimographix@gmail.com>
 * @copyright EpicFail | Studio
 * @package Barryvdh\TranslationManager\Jobs
 */
class TranslationSaveJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $locale;
    private $group;
    private $key;

    private $variables = [];

    public function handle()
    {
        Translation::firstOrCreate([
            'locale' => $this->locale,
            'group' => $this->group,
            'key' => $this->key,
        ]);

        if (count($this->variables) > 0) {
            Translation::possibleVariables($this->group, $this->key)
                ->delete();

            // save possible variables
            foreach ($this->variables as $attribute){
                \DB::table('ltm_translation_variables')->insert([
                    'group' => $this->group,
                    'key' => $this->key,
                    'attribute' => $attribute,
                ]);
            }
        }

        if(!empty($this->url)){
            // save URL with translation key
            $_testUrl = \DB::table('ltm_translation_urls')
                ->where('group', $this->group)
                ->where('key', $this->key)
                ->where('url', $this->url);

            if ($_testUrl->count() == 0) {
                \DB::table('ltm_translation_urls')->insert([
                    'group' => $this->group,
                    'key' => $this->key,
                    'url' => $this->url,
                ]);
            }
        }
    }

    public function setTranslation($locale, $group, $key)
    {
        $this->locale = $locale;
        $this->group = $group;
        $this->key = $key;

        return $this;
    }

    public function addVariable($parameter)
    {
        $this->variables[] = $parameter;
        return $this;
    }

    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }
}