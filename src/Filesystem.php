<?php
// +----------------------------------------------------------------------
// | Author: 栉风沐雨 <188150920@qq.com>
// +----------------------------------------------------------------------
// | Filesystem.php 创建时间：2016-11-23
// | 最后修改时间：2024-04-09
// +----------------------------------------------------------------------
namespace Hafly\PhpTools;

use ZipArchive;

/**
 * 文件操作类
 * @access public
 */
class Filesystem
{
    /**
     * 获取文件后缀
     * @access public
     * @param string $path 文件路径
     * @return string 文件后缀
     */
    public function getFileSuffix(string $path): string
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * 获取文件名
     * @access public
     * @param string $path 文件路径
     * @return string
     */
    public function getFileName(string $path): string
    {
        $suffix = $this->getFileSuffix($path);
        $basename = basename($path);
        return $suffix ? str_replace('.' . $suffix, '', $basename) : $basename;
    }

    /**
     * 获取文件所处路径
     * @access public
     * @param string $path
     * @return string
     */
    public function getFilePath(string $path): string
    {
        return str_replace(basename($path), '', $path);
    }

    /**
     * 去掉地址栏参数
     * @access public
     * @param $url
     * @return string
     */
    public function clearUrlQuery($url): string
    {
        $exp = explode('?', $url);
        return $exp[0];
    }

    /**
     * 创建文件夹
     * @access public
     * @param string $directory 新建文件夹路径
     * @return boolean
     */
    public function createFolder(string $directory): bool
    {
        if (!is_dir($directory)) {
            return mkdir($directory, 0777, true);
        }
        else {
            return true;
        }
    }

    /**
     * 复制/移动文件
     * @param string $from 原文件路径
     * @param string $to 目标路径
     * @param boolean $overwrite 该参数控制是否覆盖原文件
     * @param boolean $remove 复制/移动，默认复制不删除原文件
     * @return boolean
     */
    private function transformFile(string $from, string $to, bool $overwrite = true, bool $remove = false): bool
    {
        // 原文件不存在
        if (!file_exists($from)) {
            return false;
        }
        // 目标文件是否存在并覆盖
        if (file_exists($to)) {
            if ($overwrite) {
                $this->deleteFile($to);
            }
            else {
                return false;
            }
        }
        else {
            $path = dirname($to);
            if (!is_dir($path)) { // 判断目标地址是否是目录
                $this->createFolder($path);
            }
        }

        if ($remove) {
            return rename($from, $to);
        }
        else {
            return copy($from, $to);
        }
    }

    /**
     * 复制文件
     * @access public
     * @param string $form 原文件路径
     * @param string $to 目标路径
     * @param boolean $overwrite 是否覆盖原文件
     * @return boolean
     */
    public function copyFile(string $form, string $to, bool $overwrite = true): bool
    {
        return $this->transformFile($form, $to, $overwrite, false);
    }

    /**
     * 移动文件
     * @access public
     * @param string $from 原文件路径
     * @param string $to 目标路径
     * @param boolean $overwrite 是否覆盖原文件
     * @return boolean
     */
    public function moveFile(string $from, string $to, bool $overwrite = true): bool
    {
        return $this->transformFile($from, $to, $overwrite, true);
    }

    /**
     * 删除文件
     * @access public
     * @param string $filename 要删除的文件路径
     * @return boolean
     */
    public function deleteFile(string $filename): bool
    {
        if (!file_exists($filename)) {
            return false;
        }
        else {
            return unlink($filename);
        }
    }

    /**
     * 复制/移动文件夹
     * @param string $from 原路径
     * @param string $to 目标路径
     * @param boolean $overwrite 是否覆盖原文件
     * @param boolean $remove 复制/移动，默认复制不删除原文件
     * @return boolean
     */
    private function transformFolder(string $from, string $to, bool $overwrite = true, bool $remove = false): bool
    {
        if (!is_dir($from)) {
            return false;
        }
        if (!is_dir($to)) {
            $this->createFolder($to);
        }

        $dirHandle = opendir($from);
        while (false !== ($file = readdir($dirHandle))) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $old = $from . '/' . $file;
            $aim = $to . '/' . $file;
            if (!is_dir($old)) {
                $this->copyFile($old, $aim, $overwrite);
            }
            else {
                $this->copyFolder($old, $aim, $overwrite);
            }
        }
        closedir($dirHandle);
        if ($remove) {
            return $this->deleteFolder($from);
        }
        return true;
    }

    /**
     * 复制文件夹
     * @access public
     * @param string $from 原路径
     * @param string $to 目标路径
     * @param boolean $overwrite 是否覆盖原文件
     * @return boolean
     */
    public function copyFolder(string $from, string $to, bool $overwrite = true): bool
    {
        return $this->transformFolder($from, $to, $overwrite, false);
    }

    /**
     * 移动文件夹
     * @access public
     * @param string $from 原路径
     * @param string $to 目标路径
     * @param boolean $overwrite 是否覆盖原文件
     * @return boolean
     */
    public function moveFolder(string $from, string $to, bool $overwrite = true): bool
    {
        return $this->transformFolder($from, $to, $overwrite, true);
    }

    /**
     * 删除文件夹
     * @param string $directory 要删除的文件夹
     * @param boolean $deleteDir 是否保留外层文件夹
     * @return boolean
     */
    public function deleteFolder(string $directory, bool $deleteDir = true): bool
    {
        if ($directory == '' || $directory == '.' || !is_dir($directory)) {
            return false;
        }
        $dirHandle = opendir($directory);
        while (false !== ($file = readdir($dirHandle))) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $path = $directory . '/' . $file;
            //判断是文件还是文件夹
            if (!is_dir($path)) {
                $this->deleteFile($path);
            }
            else {
                $this->deleteFolder($path);
            }
        }
        closedir($dirHandle);
        if ($deleteDir) {
            return rmdir($directory);
        }
        else {
            return true;
        }
    }

    /**
     * 清空文件夹
     * @access public
     * @param string $directory 要清空的文件夹路径
     * @return boolean
     */
    public function clearFolder(string $directory): bool
    {
        return $this->deleteFolder($directory, false);
    }

    /**
     * 读取文本内容
     * @access public
     * @param string $filename 文件路径
     * @return false|string
     */
    public function readFile(string $filename): bool|string
    {
        return file_get_contents($filename);
    }

    /**
     * 写入文本内容（文件不存在则自动创建）
     * @access public
     * @param string $filename 文件路径
     * @param string $text 写入文本
     */
    public function writeFile(string $filename, string $text)
    {
        file_put_contents($filename, $text);
    }

    /**
     * 追加文本内容（文件不存在则自动创建）
     * @access public
     * @param string $filename 文件路径
     * @param string $text 写入文本
     * @param bool $newline 换行
     */
    public function appendFile(string $filename, string $text, bool $newline = true)
    {
        if ($newline) {
            $text .= PHP_EOL;
        }
        file_put_contents($filename, $text, FILE_APPEND | LOCK_EX); // 写文件的时候先锁定，防止多人同时写入造成内容丢失
    }

    /**
     * 压缩zip
     * @access public
     * @param string $directory 要压缩的文件夹路径
     * @param string $filename 压缩后的文件
     * @return bool
     */
    public function zip(string $directory, string $filename): bool
    {
        // 获取列表
        $fileList = $this->list_dir($directory);

        if (file_exists($filename)) {
            unlink($filename);
        }

        $zip = new ZipArchive();
        if ($zip->open($filename, ZipArchive::CREATE) !== TRUE) {
            exit('无法打开文件，或者文件创建失败');
        }
        foreach ($fileList as $val) {
            if (file_exists($val)) {
                $zip->addFile($val, basename($val));
            }
        }
        $zip->close();
        return file_exists($filename);
    }

    /**
     * 解压zip
     * @access public
     * @param string $filename 要解压的文件路径
     * @param string $pathto 压缩路径
     * @return bool
     */
    public function unzip(string $filename, string $pathto): bool
    {
        $zip = new ZipArchive();
        if ($zip->open($filename) !== TRUE) {
            // exit ("Could not open archive");
            return false;
        }
        $result = $zip->extractTo($pathto);
        $zip->close();
        return $result;
    }

    /**
     * 获取目录下的文件列表（递归实现）
     * @param string $directory 目录
     * @return array
     */
    private function list_dir(string $directory): array
    {
        $result = array();
        if (is_dir($directory)) {
            $file_dir = scandir($directory);
            foreach ($file_dir as $file) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                elseif (is_dir($directory . $file)) {
                    $result = array_merge($result, $this->list_dir($directory . $file . '/'));
                }
                else {
                    $result[] = $directory . '/' . $file;
                }
            }
        }
        return $result;
    }
}