<?php
class ControllerExtensionModuleAccountBlog extends Controller {
    public function index()
    {
        $this->load->language('extension/module/account_blog');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('module_account_blog', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/account_blog', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/module/account_blog', 'user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        if (isset($this->request->post['module_account_blog_status'])) {
            $data['module_account_blog_status'] = $this->request->post['module_account_blog_status'];
        } else {
            $data['module_account_blog_status'] = $this->config->get('module_account_blog_status');
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/account_blog', $data));
    }

    public function install()
    {
        $this->db->query("
            CREATE TABLE `" . DB_PREFIX . "account_blog` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `customer_id` INT NOT NULL DEFAULT 0,
                `status` TINYINT(1) NOT NULL DEFAULT 0,
                `image` VARCHAR(255) NOT NULL DEFAULT '',
                `document` VARCHAR(255) NOT NULL DEFAULT '',
                `date_added` DATETIME,
                `date_edited` DATETIME
            );
        ");

        $this->db->query("
            CREATE TABLE `" . DB_PREFIX . "account_blog_description` (
                `blog_id` INT UNSIGNED NOT NULL DEFAULT 0,
                `language_id` INT NOT NULL DEFAULT 0,
                `name` VARCHAR(255) NOT NULL DEFAULT '',
                `description` TEXT,
                `description_short` TEXT,
                PRIMARY KEY (`blog_id`, `language_id`)
            );
        ");
    }

    public function uninstall()
    {
        $this->db->query("DROP TABLE `" . DB_PREFIX . "account_blog`");
        $this->db->query("DROP TABLE `" . DB_PREFIX . "account_blog_description`");
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/account_blog')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }
}