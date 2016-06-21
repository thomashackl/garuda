<?php if ($future) : ?>
    <table class="default garuda-planned">
        <caption>
            <?= dgettext('garudaplugin', 'Zum Versand anstehende, aber noch nicht verschickte Nachrichten') ?>
        </caption>
        <colgroup>
            <col width="25%">
            <col>
            <col width="15%">
            <col width="100">
            <col width="100">
            <col width="35">
        </colgroup>
        <thead>
            <tr>
                <th><?= dgettext('garudaplugin', 'Betreff') ?></th>
                <th><?= dgettext('garudaplugin', 'Nachricht') ?></th>
                <th><?= dgettext('garudaplugin', 'Zielgruppe') ?></th>
                <th><?= dgettext('garudaplugin', 'Absender') ?></th>
                <th><?= dgettext('garudaplugin', 'Erstellt am') ?></th>
                <th><?= dgettext('garudaplugin', 'Aktion') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($future as $f) : ?>
                <tr>
                    <td><?= htmlReady($f->subject) ?></td>
                    <td>
                        <span class="garuda-more hidden-no-js<?= strlen($f->message) <= 150 ? ' hidden-js' : '' ?>">
                            <?= formatReady(substr($f->message, 0, 150)); ?>
                            <?php if (strlen($f->message) > 150) : ?>
                            <a href="">
                                ...
                                <?= dgettext('garuda', '(mehr)') ?>
                            </a>
                            <?php endif ?>
                        </span>
                        <span class="garuda-messagetext<?php strlen($f->message) > 150 ? ' hidden-js' : '' ?>"><?=
                            formatReady($f->message); ?>
                            <?php if (strlen($f->message) > 150) : ?>
                            <a href="">
                                <?= dgettext('garuda', '(weniger anzeigen)') ?>
                            </a>
                            <?php endif ?>
                        </span>
                    </td>
                    <td></td>
                    <td><?= htmlReady($f->sender ? $f->sender->getFullname() : 'Stud.IP') ?></td>
                    <td><?= date('d.m.Y H:i', $f->mkdate) ?></td>
                    <td>
                        <a href="<?= $controller->url_for('garuda/planned/delete_message', $m->id) ?>" data-confirm="<?=
                                dgettext('garudaplugin', 'Wollen Sie die Nachricht wirklich löschen?')?>">
                            <?= Icon::create('trash', 'clickable')->asImg() ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
<?php else : ?>
    <?= MessageBox::info(dgettext('garudaplugin', 'Es stehen keine Nachrichten zum Versand an.')) ?>
<?php endif ?>
