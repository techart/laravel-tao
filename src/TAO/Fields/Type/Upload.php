<?php

namespace TAO\Fields\Type;

use Illuminate\Database\Schema\Blueprint;
use TAO\Fields\Field;

/**
 * Class Upload
 * @package TAO\Fields\Type
 */
class Upload extends Field
{
    /**
     * @var bool
     */
    protected $tempId = false;

    /**
     * @param Blueprint $table
     * @return \Illuminate\Support\Fluent
     */
    public function createField(Blueprint $table)
    {
        return $table->string($this->name, 250);
    }

    /**
     * @param $request
     */
    public function setFromRequest($request)
    {
        if ($request[$this->name] == 'delete') {
            $this->delete();
            $this->item[$this->name] = '';
        }
    }

    /**
     *
     */
    public function delete()
    {
        $file = trim($this->value());
        if (!empty($file) && \Storage::exists($file)) {
            \Storage::delete($file);
        }
    }

    /**
     * @param $request
     */
    public function setFromRequestAfterSave($request)
    {
        $tid = $request[$this->name];
        $path = $this->tempDir($tid);
        if (\Storage::exists("{$path}/file") && \Storage::exists("{$path}/info.json")) {
            $info = json_decode(\Storage::get("{$path}/info.json"));
            $this->delete();
            $dest = $this->destinationPath($info);
            if (\Storage::exists($dest)) {
                \Storage::delete($dest);
            }
            \Storage::copy("{$path}/file", $dest);
            \Storage::delete("{$path}/info.json");
            \Storage::delete("{$path}/file");
            $this->item[$this->name] = $dest;
            $this->item->where($this->item->getKeyName(), $this->item->getKey())->update([$this->name => $dest]);
        }
    }

    /**
     * @param $info
     * @return string
     */
    public function destinationPath($info)
    {
        $dir = $this->param('private', false) ? $this->item->getPrivateHomeDir() : $this->item->getHomeDir();
        $file = $this->destinationFilename($info);
        return "{$dir}/{$file}";
    }

    /**
     * @param $info
     * @return mixed|null
     */
    public function destinationFilename($info)
    {
        $cb = $this->param('generate_file_name', false);
        if (is_callable($cb)) {
            return call_user_func($cb, $info);
        }

        $name = $this->param('file_name_template', '{datatype}-{field}-{id}.{ext}');
        $ext = trim($info->ext);
        if (empty($ext)) {
            $name = str_replace('.{ext}', '', $name);
            $name = str_replace('.{Ext}', '', $name);
        }
        $name = str_replace('{datatype}', $this->item->getDatatype(), $name);
        $name = str_replace('{field}', $this->name, $name);
        $name = str_replace('{id}', $this->item->getKey(), $name);
        $name = str_replace('{filename}', $info->name, $name);
        $name = str_replace('{ext}', strtolower($info->ext), $name);
        $name = str_replace('{Ext}', $info->ext, $name);

        return $name;
    }

    /**
     * @return bool|string
     */
    public function getTempId()
    {
        if (!$this->tempId) {
            $this->tempId = time() . '_' . rand(11111111, 99999999) . '_' . $this->item->getDatatype() . '_' . $this->name;
        }
        return $this->tempId;
    }

    /**
     * @param $tid
     * @return string
     */
    public function tempDir($tid)
    {
        $sid = \Session::getId();
        return "session-files/{$sid}/{$tid}";
    }

    /**
     * @param $file
     * @param $info
     * @return bool|mixed
     */
    public function checkUploadedFile($file, &$info)
    {
        $cb = $this->param('check_uploaded_file', false);
        if (is_callable($cb)) {
            return call_user_func($cb, $file);
        }
        return true;
    }

    /**
     * @param $size
     * @return string
     */
    public function generateHumanSize($size)
    {
        if ($size >= 10485760) {
            return ((int)round($size / 1048576)) . 'M';
        }
        if ($size >= 1048576) {
            return number_format($size / 1048576, 1) . 'M';
        }
        if ($size >= 10240) {
            return ((int)round($size / 1024)) . 'K';
        }
        if ($size >= 1024) {
            return number_format($size / 1024, 1) . 'K';
        }

        return $size . 'B';
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
        $file = app()->request()->file('uploadfile');
        $size = $file->getSize();
        $human_size = $this->generateHumanSize($size);

        $info = array(
            'upload_id' => $tid,
            'name' => $file->getClientOriginalName(),
            'ext' => $file->getClientOriginalExtension(),
            'mime' => $file->getClientMimeType(),
            'size' => $size,
            'human_size' => $human_size,
            'preview' => '',
        );
        $check = $this->checkUploadedFile($file, $info);
        if (is_string($check)) {
            return $check;
        }
        if (is_array($check)) {
            $info = $check;
        }
        \Storage::put("{$dir}/info.json", json_encode($info));
        $file->storeAs($dir, 'file');
        return $info;
    }

    /**
     * @return string
     */
    public function uploadUrl()
    {
        return $this->apiUrl('upload', ['_token' => csrf_token(), 'upload_id' => $this->getTempId()]);
    }

    /**
     * @return int
     */
    public function size()
    {
        $file = $this->value();
        if (empty($file)) {
            return 0;
        }
        return \Storage::size($file);
    }

    /**
     * @return string
     */
    public function humanSize()
    {
        return $this->generateHumanSize($this->size());
    }

    /**
     * @return bool
     */
    public function url()
    {
        $file = $this->value();
        if (empty($file)) {
            return false;
        }
        return \Storage::url($file);
    }

    public function renderForAdminList()
    {
        $url = $this->url();
        $value = $this->value();
        return "<a href='{$url}'>{$value}</a>";
    }
}
