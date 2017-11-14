<?php

namespace TAO\Fields\Type;

use Illuminate\Database\Schema\Blueprint;
use TAO\Fields\Field;
use TAO\Fields\FileField;

class Attaches extends StringField
{
    use FileField;

    public function createField(Blueprint $table)
    {
        return $table->text($this->name);
    }

    protected function defaultFileNameTemplate()
    {
        return '{translit}';
    }

    public function value()
    {
        $value = unserialize(parent::value());
        $out = [];
        if (is_array($value)) {
            foreach ($value as $key => $data) {
                $path = $data['path'];
                if (\Storage::exists($path)) {
                    $data['key'] = $key;
                    $data['new'] = false;
                    $data['url'] = \Storage::url($data['path']);
                    $out[$key] = $data;
                }
            }
        }
        return $out;
    }

    public function renderFilelistJSON()
    {
        return json_encode((object)$this->value());
    }

    public function setFromRequestAfterSave($request)
    {
        $out = [];
        $files = (array)json_decode($request[$this->name]);
        $dir = $this->param('private', false) ? $this->item->getPrivateHomeDir() : $this->item->getHomeDir();
        $dir = "{$dir}/{$this->name}";
        $exists = [];
        foreach ($files as $key => $data) {
            $data = (array)$data;
            if (isset($data['name']) && isset($data['path'])) {
                $name = $data['name'];
                $path = $data['path'];
                $new = isset($data['new']) ? $data['new'] : false;

                if ($new) {
                    $newPath = "{$dir}/{$name}";
                    if (\Storage::exists($newPath)) {
                        \Storage::delete($newPath);
                    }
                    \Storage::copy($path, $newPath);
                    \Storage::delete($path);
                    $data['path'] = $newPath;
                }

                unset($data['url']);
                unset($data['new']);
                unset($data['error']);
                unset($data['key']);

                $exists[$name] = $name;

                $out[$key] = $data;
            }
        }

        foreach (\Storage::files($dir) as $file) {
            $filename = basename($file);
            if (!isset($exists[$filename])) {
                \Storage::delete($file);
            }
        }

        $this->item->where($this->item->getKeyName(), $this->item->getKey())->update([$this->name => serialize($out)]);
    }


    /**
     * @return array|bool|mixed
     */
    public function apiActionUpload()
    {
        $tid = app()->request()->get('upload_id');
        $this->tempId = $tid;
        $dir = $this->tempDir($tid);
        if (!\Storage::exists($dir)) {
            \Storage::makeDirectory($dir);
        }
        $files = app()->request()->file('uploadfile');
        if (!is_array($files)) {
            $files = array($files);
        }

        $returnInfo = array();
        foreach ($files as $file) {
            $returnInfo['files'][] = $this->uploadFile($file, $tid, $dir);
        }

        return $returnInfo;
    }

    /**
     * @param $file
     * @param $tid
     * @param $dir
     * @return array|bool
     */
    protected function uploadFile($file, $tid, $dir)
    {
        $size = $file->getSize();
        $human_size = $this->generateHumanSize($size);

        $info = array(
            'upload_id' => $tid,
            'name' => $file->getClientOriginalName(),
            'ext' => $file->getClientOriginalExtension(),
            'mime' => $file->getClientMimeType(),
            'size' => $size,
            'human_size' => $human_size,
            'new' => true,
            'preview' => '',
        );
        $check = $this->checkUploadedFile($file, $info);
        if (is_string($check)) {
            return $check;
        }
        if (is_array($check)) {
            $info = $check;
        }
        $name = (string)$this->destinationFileName($info);
        $dir = rtrim($dir, '/');
        $path = "{$dir}/{$name}";
        $file->storeAs($dir, $name);

        $key = 'f' . md5($path);

        return [
            'path' => $path,
            'name' => basename($path),
            'url' => false,
            'new' => true,
            'key' => $key,
        ];
    }

}
