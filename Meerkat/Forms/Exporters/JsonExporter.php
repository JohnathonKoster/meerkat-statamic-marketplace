<?php

namespace Statamic\Addons\Meerkat\Forms\Exporters;

class JsonExporter
{

    public function export()
    {
        $submissions = $this->form()->submissions()->toArray();

        return json_encode($submissions);
    }

    public function contentType()
    {
        return 'application/json';
    }

}