<?php

namespace TAO;
use Illuminate\Contracts\View\Factory as ViewFactory;
use TAO\View\Sections;

class View
{
    public function init()
    {
    }

    public function navigation($name = 'site')
    {
        return \TAO::navigation($name);
    }

    public function meta()
    {
        return \TAO::meta();
    }

    public function setMeta($meta, $value)
    {
        return \TAO::setMeta($meta, $value);
    }

    public function hasSection($name)
    {
        return Sections::has($name);
    }

    public function yieldSection($name)
    {
        return Sections::get($name);
    }

    public function noLayout()
    {
        \TAO::useLayout(false);
    }

    public function withinLayout($layout)
    {
        \TAO::useLayout($layout);
    }

    public function renderSections($template, $context)
    {
        $r = view($template, $context);
        $sections = array();
        foreach($r->renderSections() as $section => $content) {
            $sections[$section] = $content;
            Sections::set($section, $content);
        }
        $sections['%'] = $r->render();
        return $sections;
    }

    public function render($template, $context)
    {
        $sections = $this->renderSections($template, $context);
        $content = $sections['%'];
        return $content;
    }

    public function renderWithinLayout($template, $context)
    {
        $sections = $this->renderSections($template, $context);
        $content = $sections['%'];
        $layout = \TAO::getLayout();
        if ($layout) {
            if (!isset($sections['content'])) {
                Sections::set('content', $content);
            }
            $factory = app(ViewFactory::class);
            foreach(Sections::all() as $section => $sectionContent) {
                $factory->startSection($section);
                print $sectionContent;
                $factory->stopSection();
            }
            $r = $factory->make($layout, $context);
            $content = $r->render();
        }
        return $content;
    }
}