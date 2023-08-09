<?php
!defined('EMLOG_ROOT') && exit('access denied!');

class Umami {
    private static $_instance;

    private $_is_init = false;

    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __construct()
    {
    }

    private function hookSidebar()
    {
        echo '<a class="collapse-item" id="umami" href="' . BLOG_URL . '/admin/plugin.php?plugin=umami">umami访问统计</a>';
    }

    public function init ()
    {
        if ($this->_is_init === true) {
            return;
        }
        $this->_is_init = true;

        addAction('adm_menu_ext', function () {
            $this->hookSidebar();
        });
    }

    public function success($msg, $data = '')
    {
        $result = [
            'msg' => $msg,
            'data' => $data
        ];
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        exit(0);
    }
    public function error($msg)
    {
        $result = [
            'msg' => $msg
        ];
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        exit(0);
    }

    public function getStaticMd5($file_name = 'style.css')
    {
        return md5_file(EMLOG_ROOT . '/content/plugins/umami/static/' . $file_name);
    }

    public function loadStaticPublic()
    {   $blog_url = BLOG_URL;
        echo "<link rel='stylesheet' href='{$blog_url}content/plugins/umami/static/element-plus2.3.8.css?version={$this->getStaticMd5("element-plus2.3.8.css")}'>";
        echo "<link rel='stylesheet' href='{$blog_url}content/plugins/umami/static/style.css?version={$this->getStaticMd5()}'>";
        echo "<script src='{$blog_url}content/plugins/umami/static/vue.global.prod.js?version={$this->getStaticMd5("vue.global.prod.js")}'></script>";
        echo "<script src='{$blog_url}content/plugins/umami/static/element-plus2.3.8.min.js?version={$this->getStaticMd5("element-plus2.3.8.min.js")}'></script>";
    }

    public function saveDomain ($domain) {
        // umami_domain
        $plugin_storage = Storage::getInstance('umami');
        $plugin_storage->setValue('umami_domain', $domain);
        $this->saveToken('');
        $this->success('保存成功!');
    }

    public function saveToken ($token) {
        // umami_token
        $plugin_storage = Storage::getInstance('umami');
        $plugin_storage->setValue('umami_token', $token);
    }
    public function getToken () {
        // umami_token
        $plugin_storage = Storage::getInstance('umami');
        return $plugin_storage->getValue('umami_token');
    }
    public function getDomain() {
        // umami_token
        $plugin_storage = Storage::getInstance('umami');
        return $plugin_storage->getValue('umami_domain');
    }

    public function isDomainExist() {
        // umami_token
        $plugin_storage = Storage::getInstance('umami');
        return !!$plugin_storage->getValue('umami_domain');
    }

    public function isTokenExist() {
        // umami_token
        $plugin_storage = Storage::getInstance('umami'); //使用插件的英文名称初始化一个存储实例
        return !!$plugin_storage->getValue('umami_token'); // 读取key值
    }

    private function sendGetRequest($url, $params = []) {
        // Build the query string if there are parameters
        if (!empty($params)) {
            $query = http_build_query($params);
            $url .= '?' . $query;
        }

        // Initialize cURL session
        $ch = curl_init($url);

        $headers  = [
            'Authorization: Bearer ' . $this->getToken(),
            'Content-Type: application/json'
        ];

        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response as a string
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); // set header
        // You can add more options here if needed

        // Execute cURL session and store the response
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            // Handle the error appropriately
            $this->error('cURL Error: ' . curl_error($ch));
        }

        // Close cURL session
        curl_close($ch);

        return json_decode($response, true); // Return the response from the API
    }

    private function sendPostRequest($url, $data) {
        // Initialize cURL session
        $domain = $this->getDomain();
        $ch = curl_init($domain . $url);

        $headers  = [
            'Authorization: Bearer ' . $this->getToken(),
            'Content-Type: application/json'
        ];

        // Set cURL options
        curl_setopt($ch, CURLOPT_POST, 1); // Set the HTTP method to POST
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response as a string
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); // Set the POST data
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); // set header
        // You can add more options here if needed

        // Execute cURL session and store the response
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            // Handle the error appropriately
            $this->error('cURL Error: ' . curl_error($ch));
        }

        // Close cURL session
        curl_close($ch);

        return json_decode($response, true); // Return the response from the API
    }

    public function login($username, $password) {
        $domain = $this->getDomain();
        if ($domain) {
            $response = $this->sendPostRequest('/api/auth/login', ['password' => $password, 'username' => $username]);
            $this->saveToken($response['token']);
            $this->success('登录成功', $response);
        } else {
            $this->error('请先设置umami实例地址');
        }
    }

    public function getUserInfo() {
        $url = '/api/auth/verify';

        return $this->sendPostRequest($url, []);
    }
}