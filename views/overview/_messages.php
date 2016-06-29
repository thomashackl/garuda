<table class="default garuda-messages">
    <caption>
        <?= $title ?>
    </caption>
    <colgroup>
        <col width="150">
        <col width="150">
        <col width="15%">
        <col>
        <col width="25%">
        <col width="100">
        <col width="35">
    </colgroup>
    <thead>
    <tr>
        <th><?= dgettext('garudaplugin', 'Autor') ?></th>
        <th><?= dgettext('garudaplugin', 'Absender') ?></th>
        <th><?= dgettext('garudaplugin', 'Betreff') ?></th>
        <th><?= dgettext('garudaplugin', 'Nachricht') ?></th>
        <th><?= dgettext('garudaplugin', 'Zielgruppe') ?></th>
        <th><?= dgettext('garudaplugin', 'Erstellt am') ?></th>
        <th><?= dgettext('garudaplugin', 'Aktion') ?></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($messages as $m) : ?>
        <tr>
            <td><?= htmlReady($m->author->getFullname()) ?></td>
            <td><?= htmlReady($m->sender ? $m->sender->getFullname() : 'Stud.IP') ?></td>
            <td><?= htmlReady($m->subject) ?></td>
            <td>
                        <span class="garuda-more hidden-no-js<?= strlen($m->message) <= 150 ? ' hidden-js' : '' ?>">
                            <?= formatReady(substr($m->message, 0, 150)); ?>
                            <?php if (strlen($m->message) > 150) : ?>
                                <a href="">
                                ...
                                    <?= dgettext('garuda', '(mehr)') ?>
                            </a>
                            <?php endif ?>
                        </span>
                        <span class="garuda-messagetext<?php strlen($m->message) > 150 ? ' hidden-js' : '' ?>"><?=
                            formatReady($m->message); ?>
                            <?php if (strlen($m->message) > 150) : ?>
                                <a href="">
                                <?= dgettext('garuda', '(weniger anzeigen)') ?>
                            </a>
                            <?php endif ?>
                        </span>
            </td>
            <td>
                <?php if ($m->target == 'all') : ?>
                    <?= dgettext('garudaplugin', 'Alle') ?>
                <?php elseif ($m->target == 'students') : ?>
                    <?= dgettext('garudaplugin', 'Studierende') ?>
                <?php elseif ($m->target == 'employees') : ?>
                    <?= dgettext('garudaplugin', 'Besch�ftigte') ?>
                <?php elseif ($m->target == 'list') : ?>
                    <?= dgettext('garudaplugin', 'Liste von Nutzern') ?>
                <?php endif ?>
                <?php if ($m->filters) : ?>
                    <ul>
                    <?php foreach ($m->filters as $filter) : $f = new UserFilter($filter->filter_id); ?>
                        <li><?= $f ?></li>
                    <?php endforeach ?>
                    </ul>
                <?php endif ?>
            </td>
            <td><?= date('d.m.Y H:i', $m->mkdate) ?></td>
            <td>
                <a href="<?= $controller->url_for('overview/delete_message', $m->id) ?>" data-confirm="<?=
                dgettext('garudaplugin', 'Wollen Sie die Nachricht wirklich l�schen?')?>">
                    <?= Icon::create('trash', 'clickable')->asImg() ?>
                </a>
            </td>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>