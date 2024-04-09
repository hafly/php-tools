<?php
// +----------------------------------------------------------------------
// | Author: 栉风沐雨 <188150920@qq.com>
// +----------------------------------------------------------------------
// | Http.php 创建时间：2016-11-23
// | 最后修改时间：2024-04-09
// +----------------------------------------------------------------------
namespace PhpTools;

/**
 * Http 工具类
 * @access public
 */
class Http
{
    /**
     * 下载本地文件
     * @access public
     * @param string $file_path 下载路径
     * @param string $save_path 保存路径
     */
    public function downloadFile(string $file_path, string $save_path)
    {
        $content = file_get_contents($file_path);
        file_put_contents($save_path, $content);
    }

    /**
     * 下载远程文件（需要开启php_curl扩展）
     * @access public
     * @param string $remote 远程文件名
     * @param string $local 本地保存文件名
     * @param string $referer
     * @return bool|string
     */
    public function curlDownload(string $remote, string $local, string $referer = ''): bool|string
    {
        $ch = curl_init($remote);
        $fp = fopen($local, "w");
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);       // 连接超时（秒）
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);              // 执行超时（秒）
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);    // 禁止curl验证对等证书
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($ch, CURLOPT_REFERER, $referer);
        $output = curl_exec($ch);
        curl_close($ch);
        fclose($fp);
        return $output;
    }

    /**
     * PHP+Curl伪造客户端获取页面
     * @access public
     * @param string $url 网页地址
     * @param array $options 其他入参：post,referer,cookie,headers
     * @return bool|string 返回抓取结果或者false
     */
    public function curlContent(string $url, array $options = []): bool|string
    {
        if (!$url) {
            echo 'URL is required.';
            return false;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);        // 为true时请求有返回的值
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);       // 连接超时（秒）
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);              // 执行超时（秒）
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);    // 禁止curl验证对等证书（https请求需要证书，所以https网页不一定能抓取）
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);        // 如果curl爬取过程中，设置CURLOPT_FOLLOWLOCATION为true，则会跟踪爬取重定向页面；否则，不会跟踪重定向页面。
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);   // 浏览器标识，有的网站会检查useragent

        // 设置为POST方式
        if (isset($options['post'])) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $options['post']); // 数据传输
        }

        // 伪造referer
        if (isset($options['referer'])) {
            curl_setopt($ch, CURLOPT_REFERER, $options['referer']);
        }

        // 设置cookie
        if (isset($options['cookie'])) {
            curl_setopt($ch, CURLOPT_COOKIE, $options['cookie']);
        }

        // 设置自定义的 HTTP 头部
        if (isset($options['headers'])) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $options['headers']);
        }

        $output = curl_exec($ch);
        curl_close($ch);

        if ($output === false) {
            // 返回一个保护当前会话最近一次错误的字符串
            echo 'curlContent Error: ' . curl_error($ch) . '<br>';
            return false;
        }
        return $output;
    }
}