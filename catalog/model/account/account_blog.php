<?php
class ModelAccountAccountBlog extends Model
{
    public function addItem($customer_id, $data = [])
    {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "account_blog` SET
            `customer_id` = '" . (int)$customer_id . "',
            `status` = '" . (int)$data['status'] . "',
            `image` = '" . $this->db->escape($data['image']) . "',
            `document` = '" . $this->db->escape($data['document']) . "',
            `date_added` = NOW(),
            `date_edited` = NOW()
        ");

        $item_id = $this->db->getLastId();

        foreach($data['item_description'] as $language_id => $description)
            $this->db->query("INSERT INTO `" . DB_PREFIX . "account_blog_description` SET
                `blog_id` = '" . (int)$item_id . "',
                `language_id` = '" . (int)$language_id . "',
                `name` = '" . $this->db->escape($description['name']) . "',
                `description` = '" . $this->db->escape($description['description']) . "',
                `description_short` = '" . $this->db->escape($description['description_short']) . "' 
            ");

        return $item_id;
    }

    public function editItem($customer_id, $item_id, $data = [])
    {
        $item = $this->getItem($customer_id, $item_id);

        if($item && $item['image'] && empty($data['image']))
            $this->removeImage($item['image']);

        if($item && $item['document'] && empty($data['document']))
            $this->removeDocument($item['document']);

        $this->db->query("UPDATE `" . DB_PREFIX . "account_blog` SET 
            `status` = '" . (int)$data['status'] . "',
            `image` = '" . $this->db->escape($data['image']) . "',
            `document` = '" . $this->db->escape($data['document']) . "',
            `date_edited` = NOW()
            WHERE `customer_id` = '" . (int)$customer_id . "' AND `id` = '" . (int)$item_id . "'
        ");

        $this->db->query("DELETE FROM `" . DB_PREFIX . "account_blog_description` WHERE `blog_id` = '" . (int)$item_id . "'");

        foreach($data['item_description'] as $language_id => $description)
            $this->db->query("INSERT INTO `" . DB_PREFIX . "account_blog_description` SET
                `blog_id` = '" . (int)$item_id . "',
                `language_id` = '" . (int)$language_id . "',
                `name` = '" . $this->db->escape($description['name']) . "',
                `description` = '" . $this->db->escape($description['description']) . "',
                `description_short` = '" . $this->db->escape($description['description_short']) . "' 
            ");
    }

    public function deleteItem($customer_id, $item_id)
    {
        $item = $this->getItem($customer_id, $item_id);

        if($item)
        {
            $this->removeImage($item['image']);
            $this->removeDocument($item['document']);

            $this->db->query("DELETE FROM `" . DB_PREFIX . "account_blog` WHERE `id` = '" . (int)$item_id . "'");
            $this->db->query("DELETE FROM `" . DB_PREFIX . "account_blog_description` WHERE `blog_id` = '" . (int)$item_id . "'");
        }
    }

    public function getItems($data = [])
    {
        $sql = "SELECT * FROM `" . DB_PREFIX . "account_blog` AS `ab` LEFT JOIN `" . DB_PREFIX . "account_blog_description` AS `abd` ON (`ab`.`id` = `abd`.`blog_id`)
            WHERE `abd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'
        ";

        if(isset($data['customer_id']))
            $sql .= " AND `ab`.`customer_id` = '" . (int)$data['customer_id'] . "'";

        if(isset($data['status']))
            $sql .= " AND `ab`.`status` = '" . (int)$data['status'] . "'";

        if(isset($data['start']))
        {
            if(isset($data['limit']))
                $limit = (int)$data['limit'];
            else
                $limit = 10;

            $sql .= " LIMIT " . (int)$data['start'] . ',' . $limit;
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getTotalItems($customer_id)
    {
        $query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "account_blog` WHERE `customer_id` = '" . (int)$customer_id . "'");

        return $query->row['total'];
    }

    public function getItem($customer_id, $item_id)
    {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "account_blog` WHERE `customer_id` = '" . (int)$customer_id . "' AND `id` = '" . (int)$item_id . "'");

        return $query->row;
    }

    public function getItemDescriptions($item_id)
    {
        $result = [];

        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "account_blog_description` WHERE `blog_id` = '" . (int)$item_id . "'");

        foreach($query->rows as $row)
            $result[$row['language_id']] = $row;

        return $result;
    }

    public function getDocumentDirectory()
    {
        return str_replace('catalog/', '', DIR_APPLICATION) . 'files/';
    }


    protected function removeImage($image = '')
    {
        if($image && is_file(DIR_IMAGE . $image))
        {
            $image_info = pathinfo($image);

            if(is_file(DIR_IMAGE . $image))
                unlink(DIR_IMAGE . $image);

            $files = glob(DIR_IMAGE . 'cache/' . $image_info['dirname'] . '/' . $image_info['filename'] . '*');

            foreach($files as $file)
                unlink($file);
        }
    }

    protected function removeDocument($document = '')
    {
        $directory = $this->getDocumentDirectory();

        if($document && is_file($directory . $document))
            unlink($directory . $document);
    }
}