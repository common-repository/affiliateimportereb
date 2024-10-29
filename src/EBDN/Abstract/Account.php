<?php
/**
 *
 */
if (!class_exists('EBDN_AbstractAccount')) {

    /**
     * Class EBDN_AbstractAccount
     */
    abstract class EBDN_AbstractAccount
    {

        /**
         * @var EBDN_AbstractConfigurator $api
         */
        public $api;
        public $id = false;
        public $name = '';
        public $default = true;

        /**
         * EBDN_AbstractAccount constructor.
         * @param $api
         */
        public function __construct($api)
        {
            $this->api = $api;
            $this->id = false;
            $this->default = $this->isDefaultAccount();
            $this->load();
        }

        /**
         * @return mixed
         */
        abstract protected function loadDefault();

        /**
         * @return array
         */
        abstract public function getForm();

        /**
         *
         */
        public function printForm()
        {
            $form = $this->getForm();
            foreach ($form['fields'] as $field) {
                if ($field['type'] === 'hidden') {
                    echo '<input type="hidden" id="' . $field['id'] . '" name="<?php echo $field["name"]; ?>" value="' . $field['value'] . '"/>';
                }
            }

            echo '<div class="text_content">';

            echo '<h3>' . $form['title'] . '</h3>';


            echo '<table class="settings_table">';
            foreach ($form['fields'] as $field) {
                if ($field['type'] !== 'hidden') {
                    echo '<tr valign="top">';
                    echo '<td scope="row" class="titledesc"><label for="' . $field['id'] . '">' . $field['title'] . '</label></td>';
                    echo '<td class="forminp forminp-text"><input type="text" id="' . $field['id'] . '" name="' . $field['name'] . '" value="' . $field['value'] . '"/></td>';
                    echo '</tr>';
                }
            }
            echo '</table>';

            echo '</div>';
        }

        /**
         * @param $path
         * @return bool|string
         */
        protected function getPluginData($path)
        {
            if (file_exists($path)) {
                $data = file_get_contents($path);
                if ($data) {
                    $data = base64_decode($data);
                }
                return $data;
            }
            return false;
        }

        /**
         * @return bool
         */
        public function load()
        {
            /**
             * @var wpdb $wpdb
             */
            global $wpdb;
            if ($this->default) {
                $this->loadDefault();
            } else {
                $filelds = get_object_vars($this);
                foreach ($filelds as $key => $val) {
                    $this->$key = '';
                }

                $results = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . EBDN_TABLE_ACCOUNT . " WHERE name='" . get_class($this) . "'");
                if ($results) {
                    $this->id = $results[0]->id;
                    $this->name = $results[0]->name;
                    $this->default = false;
                    $fields = unserialize($results[0]->data);
                    foreach ($fields as $key => $val) {
                        if ($key !== 'id' && $key !== 'name' && $key !== 'default') {
                            $this->$key = $val;
                        }
                    }
                    return true;
                }
            }
            return false;
        }

        /**
         * @param array $data
         */
        public function save($data = array())
        {
            if ($data && isset($data['account_type']) && $data['account_type']) {
                $form = $this->getForm();
                $this->default = false;
                update_option($form['use_default_account_option_key'], false);
            } else if (!$this->default && $data) {
                $form = $this->getForm();

                foreach ($form['fields'] as $f) {
                    $this->{$f['field']} = $data[$f['name']];
                }
                $this->name = get_class($this);

                $serializedData = serialize(get_object_vars($this));

                /**
                 * @var wpdb $wpdb
                 */
                global $wpdb;
                $wpdb->replace($wpdb->prefix . EBDN_TABLE_ACCOUNT, array('id' => $this->id, 'name' => $this->name, 'data' => $serializedData));
                $this->id = $wpdb->insert_id;
            }
        }

        /**
         * @return bool|mixed|void
         */
        public function isDefaultAccount()
        {
            return false;
        }

    }
}
