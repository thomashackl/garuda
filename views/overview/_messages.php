<table class="default garuda-messages">
    <colgroup>
        <?php if ($type == 'template') : ?>
        <col>
        <?php endif ?>
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
        <?php if ($type == 'template') : ?>
            <th><?= dgettext('garuda', 'Name') ?></th>
        <?php endif ?>
        <th><?= dgettext('garuda', 'Autor') ?></th>
        <th><?= dgettext('garuda', 'Absender') ?></th>
        <th><?= dgettext('garuda', 'Betreff') ?></th>
        <th><?= dgettext('garuda', 'Nachricht') ?></th>
        <th><?= dgettext('garuda', 'Zielgruppe') ?></th>
        <th><?= dgettext('garuda', 'Erstellt am') ?></th>
        <th><?= dgettext('garuda', 'Aktion') ?></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($messages as $m) : ?>
        <tr>
            <?php if ($type == 'template') : ?>
                <td>
                    <?= htmlReady($m->name) ?>
                </td>
            <?php endif ?>
            <td>
                <?= htmlReady($m->author->getFullname()) ?> (<?= $m->author->username?>)
            </td>
            <td>
                <?= htmlReady($m->sender ?
                    $m->sender->getFullname() . ' (' . $m->sender->username . ')' : 'Stud.IP') ?>
            </td>
            <td><?= htmlReady($m->subject) ?></td>
            <td>
                <span class="garuda-more hidden-no-js<?= strlen($m->message) <= 150 ? ' hidden-js' : '' ?>">
                    <?= formatReady(substr($m->message, 0, 150)); ?>
                    <a href="">
                        ...
                        <?= dgettext('garuda', '(mehr)') ?>
                    </a>
                </span>
                <span class="garuda-messagetext<?= strlen($m->message) > 150 ? ' hidden-js' : '' ?>"><?=
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
                    <?= dgettext('garuda', 'Alle') ?>
                <?php elseif ($m->target == 'students') : ?>
                    <?= dgettext('garuda', 'Studierende') ?>
                <?php elseif ($m->target == 'employees') : ?>
                    <?= dgettext('garuda', 'Beschäftigte') ?>
                <?php elseif ($m->target == 'courses') : ?>
                    <?= dgettext('garuda', 'Teilnehmende von Veranstaltungen') ?>
                <?php elseif ($m->target == 'list') : ?>
                    <?= dgettext('garuda', 'Liste von Nutzern') ?>
                <?php endif ?>
                <?php if (count($m->filters) > 0) : ?>
                    <ul>
                    <?php foreach ($m->filters as $filter) : $f = new UserFilter($filter->filter_id); $f->show_user_count = true; ?>
                        <li><?= $f ?></li>
                    <?php endforeach ?>
                    </ul>
                <?php endif ?>
                <?php if (count($m->courses) > 0) : ?>
                    <ul>
                    <?php foreach ($m->courses as $course) : ?>
                        <li><?= $course->getFullname() ?></li>
                    <?php endforeach ?>
                    </ul>
                <?php endif ?>
            </td>
            <td><?= date('d.m.Y H:i', $m->mkdate) ?></td>
            <td>
                <a href="<?= $controller->url_for('message/write', $type, $m->id)
                   ?>" title="<?= dgettext('garuda', 'Nachricht bearbeiten') ?>"
                   data-dialog="size='auto'">
                    <?= Icon::create('edit', 'clickable')->asImg() ?>
                </a>
                <a href="<?= $controller->url_for('overview/delete_message', $type, $m->id) ?>" data-confirm="<?=
                    $type == 'message' ?
                    dgettext('garuda', 'Wollen Sie die Nachricht wirklich löschen?') :
                    dgettext('garuda', 'Wollen Sie die Vorlage wirklich löschen?')?>">
                    <?= Icon::create('trash', 'clickable')->asImg() ?>
                </a>
            </td>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>
