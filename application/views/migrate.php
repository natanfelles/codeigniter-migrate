<?php
/**
 * CodeIgniter Migrate
 *
 * @author  Natan Felles <natanfelles@gmail.com>
 * @link    http://github.com/natanfelles/codeigniter-migrate
 */
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * @var array $assets               Assets links
 * @var bool  $migration_disabled   Migration status
 * @var array $migrations           Migration files
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Migrate</title>
    <link rel="stylesheet" href="<?= $assets['bootstrap_css'] ?>">
    <style>
        body {
            padding-top: 20px;
        }
        .full-width {
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center">
            <i class="glyphicon glyphicon-fire"></i> CodeIgniter Migrate<br>
            <small>An easy way to manage database migrations</small>
        </h1>
        <?php if (isset($migration_disabled)) : ?>
            <div class="alert alert-info">Migration is disabled.</div>
        <?php else : ?>
            <div id="msg-migrate"><div class="msg"></div></div>
            <table class="table table-striped table-hover table-bordered">
                <thead>
                    <tr>
                        <th class="text-center">Order</th>
                        <th>Version</th>
                        <th>File</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($migrations)) : ?>
                        <tr>
                            <td colspan="4">No migrations.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach (array_reverse($migrations) as $migration) : ?>
                            <tr<?= $migration['version'] != $current_version ? '' : ' class="success"'?>>
                                <th class="text-center"><?= isset($order) ? --$order : $order = count($migrations) ?></th>
                                <td><?= $migration['version'] ?></td>
                                <td><?= $migration['file'] ?></td>
                                <td>
                                    <button data-version="<?= $migration['version'] ?>" class="btn btn-sm btn-primary btn-migrate full-width">
                                        <i class="glyphicon glyphicon-refresh"></i> Migrate
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    <?php endif ?>
                </tbody>
            </table>
        <?php endif ?>
    </div>
    <script src="<?= $assets['jquery'] ?>"></script>
    <script src="<?= $assets['bootstrap_js'] ?>"></script>
    <script>
    $('.btn-migrate').click(function(){
            var btn = $(this);
            console.log(btn.data('version'));
            var d = {
                name: 'version',
                value: btn.data('version')
            };
            $.when($.ajax("<?= site_url('migrate/token') ?>", {
                cache: false
            })).done(function(t) {
                console.log(t);
                d = $.merge($.makeArray(d), $.makeArray(t));
                console.log(d);
                $.post("<?= site_url('migrate/post') ?>", d, function(r){
                    console.log(r);
                    msg('#msg-migrate', r.type, r);
                    btn.parent().parent().parent().children('tr').removeClass('success');
                    btn.parent().parent().addClass('success');
                }, 'json').fail(function(){
                    msg('#msg-migrate', 'danger', {content: 'Something is wrong.'});
                });
            });
            return false;
        });

        function msg(parent, type, r) {
            var h = '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
            if (r.header) {
                h += '<strong>' + r.header + '</strong><br>';
            }
            // If Response Content is an Object we will list it
            if (typeof r.content == 'object') {
                var o = '<ul>';
                $.each(r.content, function(k, v) {
                    o += '<li>' + v + '</li>';
                });
                o += '</ul>';
                h += o;
            } else {
                h += r.content;
            }
            $(parent).children('.msg')
                .removeClass()
                .addClass('msg alert alert-' + type)
                .html(h);
        }
    </script>
</body>
</html>
