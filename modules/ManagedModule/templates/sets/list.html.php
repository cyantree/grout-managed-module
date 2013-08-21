<?php
/** @var $this TemplateContext */
use Grout\ManagedModule\Pages\Sets\ListSetsPage;
use Cyantree\Grout\App\Generators\Template\TemplateContext;

/** @var $page ListSetsPage */
$page = $this->task->page;

$this->task->vars->set('menu', $page->type.'-sets');

$page->init();
$page->loadEntities();

echo $page->renderHeader();
echo $page->renderNavBar();
echo $page->renderTable();
echo $page->renderNavBar();
echo $page->renderFooter();