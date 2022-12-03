<?php
class ControllerBlogPost extends Controller
{
    public function index()
    {
        $this->load->language('blog/post');

        $this->load->model('account/account_blog');
        $this->load->model('account/customer');
        $this->load->model('tool/image');

        if(isset($this->request->get['page']))
        {
            $page = (int)$this->request->get['page'];
        } else
        {
            $page = 1;
        }

        if(isset($this->request->get['item_id']))
        {
            $item_id = (int)$this->request->get['item_id'];
        } else
        {
            $item_id = 0;
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
            'text' => $this->language->get('text_blog'),
            'href' => $this->url->link('blog/list', $url, true)
        );

        $post = $this->model_account_account_blog->getItem([
            'item_id' => $item_id,
            'status' => 1,
        ]);

        if($post)
        {
            if($post['image'])
            {
                $image = $this->model_tool_image->resize($post['image'], 250, 250);
            } else
            {
                $image = $this->model_tool_image->resize('no_image.png', 250, 250);
            }

            $customer_info = $this->model_account_customer->getCustomer($post['customer_id']);
            $post_descriptions = $this->model_account_account_blog->getItemDescriptions($post['id'], $this->config->get('config_language_id'));
            $post_descriptions = reset($post_descriptions);

            $this->document->setTitle($post_descriptions['name']);

            $data['title'] = $post_descriptions['name'];
            $data['description'] = $post_descriptions['description'];
            $data['date'] = date('m.d.Y', strtotime($post['date_edited']));
            $data['image'] = $image;
            $data['author'] = $customer_info['firstname'] . ($customer_info['lastname'] ? ' ' . $customer_info['lastname'] : '');
            $data['author_link'] = $this->url->link('blog/author', 'author_id=' . $post['customer_id'], true);

            $data['column_left'] = $this->load->controller('common/column_left');
            $data['column_right'] = $this->load->controller('common/column_right');
            $data['content_top'] = $this->load->controller('common/content_top');
            $data['content_bottom'] = $this->load->controller('common/content_bottom');
            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');

            $this->response->setOutput($this->load->view('blog/post', $data));
        } else
        {
            $this->document->setTitle($this->language->get('text_error'));

            $data['heading_title'] = $this->language->get('text_error');

            $data['text_error'] = $this->language->get('text_error');

            $data['continue'] = $this->url->link('common/home');

            $this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

            $data['column_left'] = $this->load->controller('common/column_left');
            $data['column_right'] = $this->load->controller('common/column_right');
            $data['content_top'] = $this->load->controller('common/content_top');
            $data['content_bottom'] = $this->load->controller('common/content_bottom');
            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');

            $this->response->setOutput($this->load->view('error/not_found', $data));
        }
    }
}