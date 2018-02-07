<?php

echo "<div class='dashboard-item'>";
echo $view->header()->setAttribute('template',$T('mail_title'));
echo "<h3 class='domain-title'>" .$T('domain_list') ."</h3>";
echo "<ul>";
foreach ($view['domains'] as $d) {
    echo "<li class='domain-list'>$d</li>";
}
echo "</ul>";
echo "</div>";

$view->includeCSS("
    .dashboard-item .domain-list {
        margin-left: 10px;
        float: right;
    }
    .dashboard-item .domain-title {
        padding: 3px;
        font-weight: bold;
    }
");
