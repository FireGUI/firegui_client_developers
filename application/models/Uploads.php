<?php
class Uploads extends CI_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->upload_folder = FCPATH . 'uploads/';
        $this->backup_folder = FCPATH . 'backup/';
    }

    public function removeUploads($files, $backup_files = true)
    {
        if ($backup_files) {
            $this->moveFiles($files);
        } else {
            $this->deleteFiles($files);
        }
    }

    public function deleteFiles($files)
    {
        if (!empty($files)) {
            if (is_array($files)) {
                foreach ($files as $file) {
                    @unlink($this->upload_folder . $file);
                }
            } else {
                @unlink($this->upload_folder . $files);
            }
        }
    }

    public function moveFiles($files)
    {
        if (!empty($files)) {
            if (is_array($files)) {
                foreach ($files as $file) {
                    rename($this->upload_folder . $file, $this->backup_folder . $file);
                }
            } else {
                rename($this->upload_folder . $files, $this->backup_folder . $files);
            }
        }
    }
}
