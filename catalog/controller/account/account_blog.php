<?php
class ControllerAccountAccountBlog extends Controller {
    protected $error = [];

    protected $image_allowed_extensions = [
        'jpg',
        'jpeg',
        'gif',
        'png',
    ];

    protected $image_allowed_mime_types = [
        'image/jpeg',
        'image/pjpeg',
        'image/png',
        'image/x-png',
        'image/gif',
    ];

    protected $document_allowed_extensions = [
        'xml',
        'xls',
        'xlsx',
        'pdf',
    ];

    protected $document_allowed_mime_types = [
        'application/pdf',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/xml',
    ];

    protected $limit = 10;

    public function index()
    {
        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('account/account', '', true);

            $this->response->redirect($this->url->link('account/login', '', true));
        }

        $this->load->language('account/account_blog');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('account/account_blog');

        $this->getList();
    }

    public function add()
    {
        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('account/account', '', true);

            $this->response->redirect($this->url->link('account/login', '', true));
        }

        $this->load->language('account/account_blog');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('account/account_blog');

        if(($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm())
        {
            $this->model_account_account_blog->addItem($this->customer->getId(), $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if(isset($this->request->get['page']))
                $url .= 'page=' . $this->request->get['page'];

            $this->response->redirect($this->url->link('account/account_blog', $url, true));
        }

        $this->getForm();
    }

    public function edit()
    {
        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('account/account', '', true);

            $this->response->redirect($this->url->link('account/login', '', true));
        }

        $this->load->language('account/account_blog');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('account/account_blog');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_account_account_blog->editItem($this->customer->getId(), $this->request->get['item_id'], $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if(isset($this->request->get['page']))
                $url .= 'page=' . $this->request->get['page'];

            $this->response->redirect($this->url->link('account/account_blog', $url, true));
        }

        $this->getForm();
    }

    public function delete()
    {
        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('account/account', '', true);

            $this->response->redirect($this->url->link('account/login', '', true));
        }

        $this->load->language('account/account_blog');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('account/account_blog');

        if(isset($this->request->get['item_id']))
        {
            $this->model_account_account_blog->deleteItem($this->customer->getId(), $this->request->get['item_id']);

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if(isset($this->request->get['page']))
                $url .= 'page=' . $this->request->get['page'];

            $this->response->redirect($this->url->link('account/account_blog', $url, true));
        }

        $this->getList();
    }

    protected function getList()
    {
        if(isset($this->request->get['page']))
        {
            $page = (int)$this->request->get['page'];
        } else
        {
            $page = 1;
        }

        $url = '';

        if(isset($this->request->get['page']))
            $url .= '&page=' . $this->request->get['page'];

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_account'),
            'href' => $this->url->link('account/account', '', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('account/account_blog', $url, true)
        );

        $data['add'] = $this->url->link('account/account_blog/add', $url, true);
        $data['back'] = $this->url->link('account/account', '', true);

        $data['items'] = [];

        $filter_data = [
            'start' => ($page - 1) * $this->limit,
            'limit' => $this->limit,
            'customer_id' => $this->customer->getId(),
        ];

        $items_total = $this->model_account_account_blog->getTotalItems(['customer_id' => $this->customer->getId()]);

        $items = $this->model_account_account_blog->getItems($filter_data);

        foreach($items as $item)
        {
            $data['items'][] = [
                'title' => $item['name'],
                'status' => $item['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
                'date_edited' => date('d.m.Y H:i:s', strtotime($item['date_edited'])),
                'actions' => [
                    'edit' => $this->url->link('account/account_blog/edit', 'item_id=' . $item['id'], true),
                    'delete' => $this->url->link('account/account_blog/delete', 'item_id=' . $item['id'], true),
                ],
            ];
        }

        if(isset($this->session->data['success']))
        {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else
        {
            $data['success'] = '';
        }

        $pagination = new Pagination();
        $pagination->total = $items_total;
        $pagination->page = $page;
        $pagination->limit = $this->limit;
        $pagination->url = $this->url->link('account/account_blog', 'page={page}', true);

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($items_total) ? (($page - 1) * $this->limit) + 1 : 0, ((($page - 1) * $this->limit) > ($items_total - $this->limit)) ? $items_total : ((($page - 1) * $this->limit) + $this->limit), $items_total, ceil($items_total / $this->limit));

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('account/account_blog_list', $data));
    }

    protected function getForm()
    {
        $url = '';

        if(isset($this->request->get['page']))
            $url .= '&page=' . $this->request->get['page'];

        if(isset($this->error['name']))
        {
            $data['error_name'] = $this->error['name'];
        } else
        {
            $data['error_name'] = [];
        }

        if(isset($this->error['description']))
        {
            $data['error_description'] = $this->error['description'];
        } else
        {
            $data['error_description'] = [];
        }

        if(isset($this->error['description_short']))
        {
            $data['error_description_short'] = $this->error['description_short'];
        } else
        {
            $data['error_description_short'] = [];
        }

        if(isset($this->error['image']))
        {
            $data['error_image'] = $this->error['image'];
        } else
        {
            $data['error_image'] = '';
        }

        if(isset($this->error['document']))
        {
            $data['error_document'] = $this->error['document'];
        } else
        {
            $data['error_document'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_account'),
            'href' => $this->url->link('account/account', '', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('account/account_blog', $url, true)
        );

        if(!isset($this->request->get['item_id']))
        {
            $data['action'] = $this->url->link('account/account_blog/add', $url, true);
        } else {
            $data['action'] = $this->url->link('account/account_blog/edit', 'item_id=' . $this->request->get['item_id'] . ($url ? '&' . $url : ''), true);
        }

        $data['back'] = $this->url->link('account/account_blog', $url, true);

        if(isset($this->request->get['item_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST'))
            $item_info = $this->model_account_account_blog->getItem([
                'item_id' => $this->request->get['item_id'],
                'customer_id' => $this->customer->getId(),
            ]);

        $this->load->model('localisation/language');

        $data['languages'] = $this->model_localisation_language->getLanguages();

        if(isset($this->request->post['item_description']))
        {
            $data['item_description'] = $this->request->post['item_description'];
        } elseif(isset($this->request->get['item_id']))
        {
            $data['item_description'] = $this->model_account_account_blog->getItemDescriptions($this->request->get['item_id']);
        } else {
            $data['item_description'] = [];
        }

        if(isset($this->request->post['status']))
        {
            $data['status'] = $this->request->post['status'];
        } elseif(!empty($item_info))
        {
            $data['status'] = $item_info['status'];
        } else
        {
            $data['status'] = '0';
        }

        $this->load->model('tool/image');

        if(isset($this->request->post['image']))
        {
            $data['image'] = $this->request->post['image'];
            $data['thumb'] = $this->model_tool_image->resize($this->request->post['image'], 100, 100);
        } elseif(!empty($item_info))
        {
            $data['image'] = $item_info['image'];
            $data['thumb'] = $this->model_tool_image->resize($item_info['image'], 100, 100);
        } else
        {
            $data['image'] = '';
            $data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
        }

        $data['no_image'] = $this->model_tool_image->resize('no_image.png', 100, 100);

        if(isset($this->request->post['document']))
        {
            $data['document'] = $this->request->post['document'];
            $data['document_name'] = basename($this->request->post['document']);
        } elseif(!empty($item_info))
        {
            $data['document'] = $item_info['document'];
            $data['document_name'] = basename($item_info['document']);
        } else
        {
            $data['document'] = '';
            $data['document_name'] = '';
        }

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('account/account_blog_form', $data));
    }

    protected function validateForm()
    {
        foreach($this->request->post['item_description'] as $language_id => $description)
        {
            if(utf8_strlen($description['name']) < 3 || utf8_strlen($description['name']) > 250)
                $this->error['name'][$language_id] = $this->language->get('error_name');

            if(utf8_strlen($description['description']) < 3 || utf8_strlen($description['description']) > 5000)
                $this->error['description'][$language_id] = $this->language->get('error_description');

            if(utf8_strlen($description['description_short']) < 3 || utf8_strlen($description['description_short']) > 500)
                $this->error['description_short'][$language_id] = $this->language->get('error_description_short');
        }

        if(isset($this->request->files['image']) && $this->request->files['image']['size'])
        {
            if(is_file($this->request->files['image']['tmp_name']))
            {
                $image = $this->request->files['image'];

                $image_info = pathinfo($image['name']);

                if((utf8_strlen($image_info['basename']) < 3) || (utf8_strlen($image_info['basename']) > 255))
                    $this->error['image'] = $this->language->get('error_filename');

                if(!in_array($image_info['extension'], $this->image_allowed_extensions))
                    $this->error['image'] = $this->language->get('error_filetype');

                if(!in_array($image['type'], $this->image_allowed_mime_types))
                    $this->error['image'] = $this->language->get('error_filetype');

                if($image['size'] > $this->config->get('config_file_max_size'))
                    $this->error['image'] = $this->language->get('error_filesize');

                if($image['error'] != UPLOAD_ERR_OK)
                    $this->error['image'] = $this->language->get('error_upload_' . $image['error']);
            } else
            {
                $this->error['image'] = $this->language->get('error_upload');
            }

            if(!isset($this->error['image']))
            {
                $filename = 'blog/' . $image_info['filename'] . date('YmdHis') . '.' . $image_info['extension'];

                $this->request->post['image'] = $filename;

                move_uploaded_file($image['tmp_name'], DIR_IMAGE . $filename);
            }
        }

        if(isset($this->request->files['document']) && $this->request->files['document']['size'])
        {
            if(is_file($this->request->files['document']['tmp_name']))
            {
                $document = $this->request->files['document'];

                $document_info = pathinfo($document['name']);

                if((utf8_strlen($document_info['basename']) < 3) || (utf8_strlen($document_info['basename']) > 255))
                    $this->error['document'] = $this->language->get('error_filename');

                if(!in_array($document_info['extension'], $this->document_allowed_extensions))
                    $this->error['document'] = $this->language->get('error_filetype');

                if(!in_array($document['type'], $this->document_allowed_mime_types))
                    $this->error['document'] = $this->language->get('error_filetype');

                if($document['size'] > $this->config->get('config_file_max_size'))
                    $this->error['document'] = $this->language->get('error_filesize');

                if($document['error'] != UPLOAD_ERR_OK)
                    $this->error['document'] = $this->language->get('error_upload_' . $document['error']);
            } else
            {
                $this->error['document'] = $this->language->get('error_upload');
            }

            if(!isset($this->error['document']))
            {
                $filename = $document_info['filename'] . date('YmdHis') . '.' . $document_info['extension'];

                $this->request->post['document'] = $filename;

                $directory = $this->model_account_account_blog->getDocumentDirectory();

                move_uploaded_file($document['tmp_name'], $directory . $filename);
            }
        }

        return !$this->error;
    }
}