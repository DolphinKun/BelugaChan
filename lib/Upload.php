<?php

class Upload extends Bans
{
    protected $config;

    public function IsImage($file_extension)
    {
        // Convert to lowercase to prevent any issues
        $file_extension = strtolower($file_extension);
        if (in_array($file_extension, $this->config["image_formats"])) {
            return true;
        } else {
            return false;
        }
    }

    public function MoveFile($files, $board, $file_count)
    {
        $file_details = [];
        if ($files['files']['name'][0] !== "") {
            // Check if board file dir exists
            if (!is_dir($this->config["file_dir"] . $board)) {
                mkdir($this->config["file_dir"] . $board);
            }
            for ($i = 0; $i < $file_count; $i++) {
                $tmp_file_path = $files['files']['tmp_name'][$i];
                $file_extension = pathinfo($files['files']['name'][$i], PATHINFO_EXTENSION);
                if ($tmp_file_path != "") {
                    $file_name = bin2hex(random_bytes(16));
                    $new_file_path = $this->config["file_dir"] . $board . "/" . $file_name . "." . $file_extension;

                    if (move_uploaded_file($tmp_file_path, $new_file_path)) {
                        $file_mime_type = mime_content_type($new_file_path);
                        if (!in_array($file_mime_type, $this->config["file_mimetypes"])) {
                            unlink($new_file_path);
                            header("Location: " . $this->config["access_point"] . "error?error=BAD_FILE");
                            die();
                        }
                        // Check if file is banned
                        $file_checksum = sha1_file($new_file_path);
                        if ($this->CheckIfFileIsBanned($file_checksum)) {
                            unlink($new_file_path);
                            header("Location: " . $this->config["access_point"] . "error?error=BAD_FILE");
                            die();
                        }
                        $max_upload_size = round($this->config["max_upload_size"] * 1000 * 1000);
                        if (filesize($new_file_path) > $max_upload_size) {
                            unlink($new_file_path);
                            header("Location: " . $this->config["access_point"] . "error?error=BAD_FILE");
                            die();
                        }
                        $original_file_name = filter_var($files['files']['name'][$i], FILTER_SANITIZE_STRING);
                        $file_details[$i] = [
                            "file_name" => $file_name . "." . $file_extension,
                            "original_file_name" => $original_file_name,
                            "file_size" => number_format(filesize($new_file_path) / 1048576, 2),
                        ];
                        if ($this->config["enable_thumbnails"] && $this->IsImage($file_extension)) {
                            $thumbnail = $file_name . "_thumb." . $file_extension;
                            $file_details[$i]["thumbnail"] = $thumbnail;
                            if (!$this->config["external_thumbnail_processor"]) {
                                try {
                                    $imagick = new \Imagick(realpath($new_file_path));
                                    $imagick->thumbnailImage(100, 100, true, true);
                                    $thumbnail_path = $this->config["file_dir"] . $board . "/" . $thumbnail;
                                    $imagick->writeImage($thumbnail_path);
                                    $imagick->clear();
                                    $imagick->destroy();
                                } catch (Exception $e) {
                                    die("Thumbnail error: " . $e->getMessage());
                                }
                            }
                        }
                    }
                }
            }
            $file_info = json_encode($file_details);
            if (!$file_details) {
                return false;
            }
            return $file_info;
        }
        return false;
    }

    public function UploadBanner($files, $board)
    {
        if ($files['file']['name'] !== "") {
            $file_info = "";
            // Check if board file dir exists
            if (!is_dir($this->config["file_dir"] . $board)) {
                mkdir($this->config["file_dir"] . $board);
            }
            // Check if board banner dir exists
            if (!is_dir($this->config["file_dir"] . $board . "/banners")) {
                mkdir($this->config["file_dir"] . $board . "/banners");
            }
            $tmp_file_path = $files['file']['tmp_name'];
            $file_extension = pathinfo($files['file']['name'], PATHINFO_EXTENSION);
            if ($tmp_file_path != "") {
                $file_name = bin2hex(random_bytes(16));
                $new_file_path = $this->config["file_dir"] . $board . "/banners/" . $file_name . "." . $file_extension;

                if (move_uploaded_file($tmp_file_path, $new_file_path)) {
                    $file_mime_type = mime_content_type($new_file_path);
                    if (!in_array($file_mime_type, $this->config["file_mimetypes"])) {
                        unlink($new_file_path);
                        header("Location: " . $this->config["access_point"] . "error?error=BAD_FILE");
                        die();
                    }
                    // Check if file is banned
                    $file_checksum = sha1_file($new_file_path);
                    if ($this->CheckIfFileIsBanned($file_checksum)) {
                        unlink($new_file_path);
                        header("Location: " . $this->config["access_point"] . "error?error=BAD_FILE");
                        die();
                    }
                    $max_upload_size = round($this->config["max_upload_size"] * 1000 * 1000);
                    if (filesize($new_file_path) > $max_upload_size) {
                        unlink($new_file_path);
                        header("Location: " . $this->config["access_point"] . "error?error=BAD_FILE");
                        die();
                    }
                    if(!$this->IsImage($file_extension)) {
                        unlink($new_file_path);
                        header("Location: " . $this->config["access_point"] . "error?error=BAD_FILE");
                        die();
                    }
                    $file_info = $file_name . "." . $file_extension;
                }
            } else {
                return false;
            }
            return $file_info;
        }
    }
}
