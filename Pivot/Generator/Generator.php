<?php


namespace AcMarche\Pivot\Generator;

use AcMarche\Pivot\Repository\PivotRemoteRepository;

class Generator
{
    public function generateType()
    {
        $pivotRepository = new PivotRemoteRepository();
        $types           = json_decode($pivotRepository->getTypes());

        foreach ($types as $rows) {
            foreach ($rows as $type) {
                var_dump($type->label[0]->value);//Evènement
                var_dump($type->code);//EVT
                var_dump($type->order);//9
            }
        }
    }

    public function getFields(int $type)
    {
        $pivotRepository = new PivotRemoteRepository();
        $types           = json_decode($pivotRepository->getFields($type));

        foreach ($types->spec as $spec) {
            var_dump($spec->label[0]->value);
            foreach ($spec->spec as $field) {
                var_dump($field->label[0]->value);
            }
        }
    }
}
