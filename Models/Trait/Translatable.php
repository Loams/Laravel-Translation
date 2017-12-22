<?php
/**
 * Created by PhpStorm.
 * User: stephane
 * Date: 22/12/17
 * Time: 16:57
 */

trait Translatable
{
    /**
     * Return Translation model of parent model
     *
     * @return mixed
     */
    private function getTranslationModel()
    {
        $this->checkIfModelHaveTranslationModelFQQCNVariable();
        return new $this->translationModelFqcn;
    }

    /**
     * Check if parent model have $translation_model_fqcn variable
     *
     * @throws TranslationException
     */
    private function checkIfModelHaveTranslationModelFQQCNVariable(): void
    {
        if(!property_exists($this, 'translation_model_fqcn') && $this->translationModelFqcn !== '') {
            throw new TranslationException('Your model don\'t have the variable translation_model_fqcn');
        }
    }

    /**
     * Relationship
     *
     * @return HasMany
     */
    public function translations():HasMany
    {
        $model = $this->getModel();
        return $this->hasMany($this->translationModelFqcn, $model->foreign_key);
    }

    /**
     * Get one translation if exist or return default translation
     *
     * @param null|string $language
     * @param null|string $domain
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    public function translation(string $language = null, string $domain = null)
    {
        $model = $this->getModel();

        if(is_null($language)) {
            $language = domain()->get('locale') ;
        }
        $query = $this->translations()->where($model->getTable() . '.language', $language)->first();
        if(is_null($query)) {
            return $this->buildDefaulttranslation();
        }
        return $query;
    }

    /**
     * Generate the default translation
     *
     * @return mixed
     */
    private function buildDefaulttranslation()
    {
        $model = $this->getModel();
        $foreign_key = $model->foreign_key;

        foreach($model->getFillable() as $attribute) {
            if(in_array($attribute, $this->getFillable()) && $attribute !== 'id') {
                $model->$attribute = $this->$attribute;
            }
            $model->$foreign_key = $this->id;
            $model->language = domain('locale');
        }
        return $model;
    }

    /**
     * Get active language
     *
     * @param array $available_languages
     */
    public function getActiveLanguage(array $available_languages)
    {
        foreach($available_languages as $language) {
            $translation = $this->translation($language);
            $language_active = $translation->id > 0;
            $active_language[$language] = $language_active;
        }
        return $active_language;
    }
}