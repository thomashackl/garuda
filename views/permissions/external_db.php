<form class="default" action="<?= $controller->url_for('permissions/save_external_db')  ?>" method="post">
    <fieldset name="database">
        <legend>
            <?= dgettext('garudaplugin', 'Datenbankeinstellungen') ?>
        </legend>
        <label>
            <?= dgettext('garudaplugin', 'Datenbanktyp') ?>
            <select name="dbtype" size="1">
                <option name="mysql"<?= $config['dbtype'] == 'mysql' ? ' selected' : '' ?>>MySQL</option>
                <option name="informix""<?= $config['dbtype'] == 'informix' ? ' selected' : '' ?>>Informix</option>
            </select>
        </label>
        <label>
            <?= dgettext('garudaplugin', 'Servername/IP') ?>
            <input type="text" name="hostname" size="75" value="<?= htmlReady($config['hostname']) ?>">
        </label>
        <label>
            <?= dgettext('garudaplugin', 'Datenbank') ?>
            <input type="text" name="database" size="75" value="<?= htmlReady($config['database']) ?>">
        </label>
        <label>
            <?= dgettext('garudaplugin', 'Benutzername') ?>
            <input type="text" name="username" size="75" value="<?= htmlReady($config['username']) ?>">
        </label>
        <label>
            <?= dgettext('garudaplugin', 'Passwort') ?>
            <input type="password" name="password" size="75" value="<?= htmlReady($config['password']) ?>">
        </label>
    </fieldset>
    <fieldset name="additional"<?= $config['dbtype'] == 'informix' ? '' : ' class="hidden-js"' ?>>
        <legend>
            <?= dgettext('garudaplugin', 'Weitere Einstellungen') ?>
        </legend>
        <label>
            <?= dgettext('garudaplugin', 'Informixclient-Verzeichnis') ?>
            <input type="text" name="informixdir" size="75" value="<?= htmlReady($config['informixdir']) ?>">
        </label>
        <label>
            <?= dgettext('garudaplugin', 'Protokoll') ?>
            <select name="protocol" size="1">
                <?php foreach (words('onsoctcp drsoctcp onipcstr onipcshm onsocimc drsocssl onsocssl') as $p) : ?>
                    <option value="<?= $p ?>"<?= $p == $config['protocol'] ? ' selected' : '' ?>><?= $p ?></option>
                <?php endforeach ?>
            </select>
        </label>
        <label>
            <?= dgettext('garudaplugin', 'Clientlocale') ?>
            <input type="text" name="client_locale" size="75" value="<?= htmlReady($config['client_locale']) ?>">
        </label>
        <label>
            <?= dgettext('garudaplugin', 'Datenbanklocale') ?>
            <input type="text" name="db_locale" size="75" value="<?= htmlReady($config['db_locale']) ?>">
        </label>
    </fieldset>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(dgettext('garudaplugin', 'Speichern'), 'submit') ?>
    </footer>
</form>
