<?php if ($templates) : ?>
    <?= $this->render_partial('overview/_messages.php',
        array(
            'messages' => $templates,
            'type' => 'template')
    ) ?>
<?php else : ?>
    <?= MessageBox::info(dgettext('garudaplugin', 'Es sind keine Nachrichtenvorlagen vorhanden.')) ?>
<?php endif ?>
