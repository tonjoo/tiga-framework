<?php

namespace Tiga\Framework;

/**
 * Pagination class.
 */
class Pagination
{
    /**
     * @var array
     */
    private $config = array();

    /**
     * Initialize pagination config.
     *
     * @param array $config
     */
    public function init($config)
    {
        // init init error

        //default
        $this->config['per_page'] = 10;
        $this->config['page'] = 0;
        $this->config['item_to_show'] = 2;
        $this->config['skip_item'] = true;
        $this->config['request_page'] = 'page';

        $this->config['first_tag_open'] = '<li>';
        $this->config['first_tag_close'] = '</li>';

        $this->config['last_tag_open'] = '<li>';
        $this->config['last_tag_close'] = '</li>';

        $this->config['link_attribute'] = '';
        $this->config['link_attribute_active'] = '';

        $this->config['prev_tag_open'] = '<li>';
        $this->config['prev_tag_close'] = '</li>';
        $this->config['prev_tag_text'] = 'Prev';

        $this->config['next_tag_open'] = '<li>';
        $this->config['next_tag_close'] = '</li>';
        $this->config['next_tag_text'] = 'Next';

        $this->config['cur_tag_open'] = "<li class='active'>";
        $this->config['cur_tag_close'] = '</li>';

        $this->config['num_tag_open'] = '<li>';
        $this->config['num_tag_close'] = '</li>';

        $this->config['skip_tag_open'] = '<li>';
        $this->config['skip_tag_close'] = '</li>';
        $this->config['skip_tag_text'] = "<a href='#'>....</a>";

        //merge options
        foreach ($config as $key => $value) {
            $this->config[$key] = $value;
        }

        if ($this->config['item_to_show'] < 2) {
            $this->config['item_to_show'] = 2;
        }

        $this->total = intval($config['rows']);
        $this->per_page = intval($config['per_page']);
        $this->current_page = intval($config['current_page']);
        $this->base_url = urldecode($config['base_url']);
    }

    /**
     * Return the number of offset for given convig.
     *
     * @return int
     */
    public function offsett()
    {
        //calculate offset
        return $this->per_page * $this->current_page;
    }

    /**
     * Render the pagination link.
     *
     * @return string
     */
    public function render()
    {

        //calculate iteration
        if($this->per_page == 1)
        {
            $iteration = (int) ceil($this->total / $this->per_page);            
        }else{
            $iteration = (int) ceil($this->total / $this->per_page) - 1;            
        }

        if ($iteration == 0) {
            return;
        }

        $item_to_show = $this->config['item_to_show'];

        $first_item_max = $item_to_show  - 1;
        $last_item_min = $iteration + 1 - $item_to_show;

        $print_array = array();

        if ($this->config['skip_item']) {

            //calculate pagination print
            for ($i = 0;$i <= $first_item_max;++$i) {
                $print_array[$i] = true;
            }

            for ($i = $last_item_min;$i <= $iteration;++$i) {
                $print_array[$i] = true;
            }

            if (!isset($print_array[$this->current_page - $item_to_show - 1])) {
                $print_array[$this->current_page - $item_to_show - 1] = 'skip';
            }

            if (!isset($print_array[$this->current_page + $item_to_show + 1])) {
                $print_array[$this->current_page + $item_to_show + 1] = 'skip';
            }

            for ($i = $this->current_page;$i <= $this->current_page + $item_to_show;++$i) {
                $print_array[$i] = true;
            }
            for ($i = $this->current_page - $item_to_show;$i <= $this->current_page;++$i) {
                $print_array[$i] = true;
            }
        }


        for ($i = 0;$i <= $iteration;++$i) {
            if ($this->config['skip_item']) {
                if (!isset($print_array[$i])) {
                    continue;
                }

                if ($print_array[$i] === 'skip') {
                    echo $this->config['skip_tag_open'];
                    echo "{$this->config['skip_tag_text']}";
                    echo $this->config['skip_tag_close'];
                    continue;
                }
            }

            $page_number = $i + 1;


            //prev
            if ($i == 0) {
                if ($this->current_page == 1) {
                    $prev_url = str_replace('$paginate', '', $this->base_url);
                } elseif ($i != $this->current_page) {
                    $prev_page = $this->current_page;
                    $prev_url = str_replace('$paginate', $this->current_page - 1, $this->base_url.'?'.$this->config['request_page'].'='.$prev_page);
                } else {
                    $prev_url = '#';
                }

                echo $this->config['prev_tag_open'];
                echo "<a ".$this->config['link_attribute']." href='{$prev_url}'>{$this->config['prev_tag_text']}</a>";
                echo $this->config['prev_tag_close'];
            }

            $url = str_replace('$paginate', $i, $this->base_url);

            //current
            if ($i == $this->current_page) {
                echo $this->config['cur_tag_open'];
                echo "<a ".$this->config['link_attribute_active']." href='{$url}?".$this->config['request_page']."={$page_number}'>$page_number </a>";
                echo $this->config['cur_tag_close'];
            }
            //first
            elseif ($i == 0) {
                $url = str_replace('$paginate', '', $this->base_url);

                echo $this->config['first_tag_open'];
                echo "<a ".$this->config['link_attribute']." href='{$url}?".$this->config['request_page']."={$page_number}'>$page_number </a>";
                echo $this->config['first_tag_close'];
            }
            //last
            elseif ($i == $iteration) {
                echo $this->config['last_tag_open'];
                echo "<a ".$this->config['link_attribute']." href='{$url}?".$this->config['request_page']."={$page_number}'>$page_number </a>";
                echo $this->config['last_tag_close'];
            } else {
                echo $this->config['num_tag_open'];
                echo "<a ".$this->config['link_attribute']." href='{$url}?".$this->config['request_page']."={$page_number}'>$page_number </a>";
                echo $this->config['num_tag_close'];
            }

            //next
            if ($i == $iteration) {
                if ($i != $this->current_page) {
                    $nex_page = $this->current_page + 2;
                    $next_url = str_replace('$paginate', $this->current_page + 1, $this->base_url.'?'.$this->config['request_page'].'='.$nex_page);
                } else {
                    $next_url = '#';
                }

                echo $this->config['next_tag_open'];
                echo "<a ".$this->config['link_attribute']." href='{$next_url}'>{$this->config['next_tag_text']}</a>";
                echo $this->config['next_tag_close'];
            }

            $print_skip = true;
        }
    }
}
