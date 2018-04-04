<?php

namespace Craft;

class CategoryDropdownFieldType extends CategoriesFieldType
{
    protected $sortable = true;

    public function getName()
    {
        return Craft::t('Category Dropdown');
    }

    /**
     * @inheritDoc ISavableComponentType::getSettingsHtml()
     *
     * @return string|null
     */
    public function getSettingsHtml()
    {
        return craft()->templates->render('_components/fieldtypes/elementfieldsettings', array(
            'allowMultipleSources'  => $this->allowMultipleSources,
            'allowLimit'            => false,
            'sources'               => $this->getSourceOptions(),
            'targetLocaleFieldHtml' => $this->getTargetLocaleFieldHtml(),
            'viewModeFieldHtml'     => $this->getViewModeFieldHtml(),
            'settings'              => $this->getSettings(),
            'defaultSelectionLabel' => Craft::t('Enter text here to add a blank value w/ label'),
            'type'                  => $this->getName()
        ));
    }

    /**
     * @inheritDoc IFieldType::prepValueFromPost()
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function prepValueFromPost($value)
    {
        $value = parent::prepValueFromPost($value);

        $value = array_filter($value, function($id) {
            return $id !== '';
        });

        return $value ? $value : null;
    }

    /**
     * @inheritDoc IFieldType::getInputHtml()
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return string
     */
    public function getInputHtml($name, $criteria)
    {
        $settings = $this->getSettings();

        $sourceKey = $settings->source;

        if ($sourceKey) {
            $source = $this->getElementType()->getSource($sourceKey, 'field');
        }

        if (empty($source)) {
            return '<p class="error">'.Craft::t('This field is not set to a valid category group.').'</p>';
        }

        if (! $criteria instanceof ElementCriteriaModel) {
            $criteria = craft()->elements->getCriteria(ElementType::Category);
            $criteria->id = false;
        }

        $category = $criteria->first();

        $value = $category ? $category->id : null;

        $options = array();

        $selectionLabel = $settings->selectionLabel;

        if ($selectionLabel) {
            $options[''] = $selectionLabel;
        }

        $categories = craft()->elements->getCriteria(ElementType::Category);

        foreach ($source['criteria'] as $k => $v) {
            $categories->$k = $v;
        }

        foreach ($categories as $i => $element) {
            $prefix = $element->level > 1 ? str_repeat('–', $element->level - 1).' ' : '';
            $options[$element->id] = $prefix.$element->title;
        }

        return craft()->templates->render('_includes/forms/select', array(
            'name'    => $name.'[]',
            'value'   => $value,
            'options' => $options,
        ));
    }

    /**
     * @inheritDoc IFieldType::onAfterElementSave()
     *
     * @return null
     */
    public function onAfterElementSave()
    {
        $categoryIds = $this->element->getContent()->getAttribute($this->model->handle);

        // Make sure something was actually posted
        if ($categoryIds) {
            // Fill in any gaps
            $categoryIds = craft()->categories->fillGapsInCategoryIds($categoryIds);

            // shift the selection to the front
            $selectedCategoryId = array_pop($categoryIds);

            array_unshift($categoryIds, $selectedCategoryId);

            craft()->relations->saveRelations($this->model, $this->element, $categoryIds);
        } else {
            craft()->relations->saveRelations($this->model, $this->element, []);
        }
    }
}
