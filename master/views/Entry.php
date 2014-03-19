<?php

class EntryModel {
        public $date;
        public $amount;

        public function update_date($date) {
                $this->date = $date;
        }

        public function update_amount($amount) {
                $this->amount = $amount;
        }
}

class EntryController {
        private $model;

        public function __construct(EntryModel $model) {
                $this->model = $model;
        }

        public function get_submission() {
                if (!isset($_POST['submitted']))
                        return;

                foreach ($_POST as $g => $k)
                {
                        if ($g == 'form_date')
                                $this->model->update_date($k);
                        elseif ($g == 'form_amount')
                                $this->model->update_amount($k);
                }

                echo ("Sucessful submission");
        }

}

class EntryView {
        private $model;
        private $controller;

        public function __construct(EntryController $controller, EntryModel $model) {
                $this->controller = $controller;
                $this->model = $model;
        }

        public function output() {
                $form_output =
                '<form action="index.php" method="POST">
                        <div>Date: <input type="date" name="form_date" value="' . $this->model->date . '"></div>
                        <div>Amount: <input type="number" name="form_amount" value="' . $this->model->amount . '"></div>
                        <input type="hidden" name="submitted" value="yes">
                        <input type="submit">
                </form>';

                return $form_output;
        }
}

$model = new EntryModel();
$controller = new EntryController($model);
$view = new EntryView($controller, $model);

$controller->get_submission();

echo $view->output();

?>