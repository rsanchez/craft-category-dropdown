<?php

namespace Craft;

class CategoryDropdownPlugin extends BasePlugin
{
    public function getName()
    {
        return Craft::t('Category Dropdown');
    }

    public function getVersion()
    {
        return '1.0.3';
    }

    public function getDeveloper()
    {
        return 'Rob Sanchez';
    }

    public function getDeveloperUrl()
    {
        return 'https://github.com/rsanchez';
    }
}
