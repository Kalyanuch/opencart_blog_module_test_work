<?php
class ControllerBlogList extends Controller
{
    protected $limit = 10;

    public function index()
    {
        $this->load->language('blog/list');

        $this->document->setTitle($this->language->get('heading_title'));

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

        $url = '';

        if(isset($this->request->get['page']))
            $url .= '&page=' . $this->request->get['page'];

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('blog/list', $url, true)
        );

        $data['items'] = [];

        $filter_data = [
            'start' => ($page - 1) * $this->limit,
            'limit' => $this->limit,
            'status' => 1,
        ];

        $items_total = $this->model_account_account_blog->getTotalItems($this->customer->getId());

        $items = $this->model_account_account_blog->getItems($filter_data);

        foreach($items as $item)
        {
            $customer_info = $this->model_account_customer->getCustomer($item['customer_id']);

            if($item['image'])
            {
                $image = $this->model_tool_image->resize($item['image'], 250, 250);
            } else
            {
                $image = $this->model_tool_image->resize('no_image.png', 250, 250);
            }

            $data['items'][] = [
                'title' => $item['name'],
                'date' => date('d.m.Y', strtotime($item['date_edited'])),
                'author' => $customer_info['firstname'] . ($customer_info['lastname'] ? ' ' . $customer_info['lastname'] : ''),
                'author_link' => $this->url->link('blog/author', 'author_id=' . $item['customer_id'], true),
                'thumb' => $image,
                'link' => $this->url->link('blog/post', 'item_id=' . $item['id'], true),
                'description' => html_entity_decode($item['description_short'], ENT_QUOTES, 'utf-8'),
            ];
        }

        $pagination = new Pagination();
        $pagination->total = $items_total;
        $pagination->page = $page;
        $pagination->limit = $this->limit;
        $pagination->url = $this->url->link('blog/list', 'page={page}', true);

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($items_total) ? (($page - 1) * $this->limit) + 1 : 0, ((($page - 1) * $this->limit) > ($items_total - $this->limit)) ? $items_total : ((($page - 1) * $this->limit) + $this->limit), $items_total, ceil($items_total / $this->limit));

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('blog/list', $data));
    }
}