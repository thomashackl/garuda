<article class="studip toggle" id="importsettings">
    <header>
        <h1>
            <a href="<?= ContentBoxHelper::href('importsettings') ?>">
                <?= Icon::create('admin', 'clickable') ?>
                <?= dgettext('garudaplugin', 'Importeinstellungen') ?>
            </a>
        </h1>
    </header>
    <section>
        <form class="default" action="<?= $controller->url_for('permissions/save_external_db') ?>" method="post">
            <fieldset name="enable">
                <legend>
                    <?= dgettext('garudaplugin', 'Import von Zuordnungen') ?>
                </legend>
                <input type="checkbox" name="enable"<?= $enabled ? ' checked' : '' ?>>
                <?= dgettext('garudaplugin', 'aktiviert') ?>
            </fieldset>
            <fieldset name="database"<?= $enabled ? '' : ' class="hidden-js"' ?>>
                <legend>
                    <?= dgettext('garudaplugin', 'Datenbankverbindung') ?>
                </legend>
                <label>
                    <?= dgettext('garudaplugin', 'Typ') ?>
                    <select name="dbtype" size="1">
                        <option value="mysql"<?= $config['dbtype'] == 'mysql' ? ' selected' : '' ?>>MySQL</option>
                        <option value="informix"<?= $config['dbtype'] == 'informix' ? ' selected' : '' ?>>Informix</option>
                    </select>
                </label>
                <label>
                    <?= dgettext('garudaplugin', 'Server') ?>
                    <input type="text" name="hostname" size="75" value="<?= htmlReady($config['hostname']) ?>">
                </label>
                <label>
                    <?= dgettext('garudaplugin', 'Name der Datenbank') ?>
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
            <fieldset name="tableinfo">
                <legend>
                    <?= dgettext('garudaplugin', 'Datenabgleich') ?>
                </legend>
                <label>
                    <?= dgettext('garudaplugin', 'Tabellenname') ?>
                    <input type="text" name="table" size="75" value="<?= htmlReady($config['table']) ?>">
                </label>
                <label>
                    <?= dgettext('garudaplugin', 'Spalte für Abschlussnamen') ?>
                    <input type="text" name="degrees" size="75" value="<?= htmlReady($config['degrees']) ?>">
                </label>
                <label>
                    <?= dgettext('garudaplugin', 'Spalte für Fächernamen') ?>
                    <input type="text" name="subjects" size="75" value="<?= htmlReady($config['subjects']) ?>">
                </label>
                <label>
                    <?= dgettext('garudaplugin', 'Spalte für Einrichtungen') ?>
                    <input type="text" name="institutes" size="75" value="<?= htmlReady($config['institutes']) ?>">
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
                    <?= dgettext('garudaplugin', 'Servicename') ?>
                    <input type="text" name="service" size="75" value="<?= htmlReady($config['service']) ?>">
                </label>
                <label>
                    <?= dgettext('garudaplugin', 'Informixservername') ?>
                    <input type="text" name="server" size="75" value="<?= htmlReady($config['server']) ?>">
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
            <fieldset name="mapping">
                <legend>
                    <?= dgettext('garudaplugin', 'Verknüpfung der Einrichtungs-IDs') ?>
                </legend>
                <section>
                    <?= Studip\LinkButton::create(dgettext('garudaplugin', 'Einrichtungen zuordnen'), $controller->url_for('permissions/match_institutes'), ['data-dialog' => '']) ?>
                </section>
            </fieldset>
            <?= CSRFProtection::tokenTag() ?>
            <footer data-dialog-button>
                <?= Studip\Button::createAccept(dgettext('garudaplugin', 'Speichern'), 'submit') ?>
            </footer>
        </form>
    </section>
</article>
<?php if ($enabled) : ?>
    <form class="default" action="<?= $controller->url_for('permissions/match_institutes') ?>" method="post">
        <div class="messagebox messagebox_warning">
            <?= dgettext('garudaplugin',
                        'Wenn Sie die Zuordnungen importieren, werden alle '.
                        'bestehenden Zuordnungen zu den gefundenen Einrichtungen '.
                        'überschrieben!') ?>
        </div>
        <?= CSRFProtection::tokenTag() ?>
        <footer data-dialog-button>
            <?= Studip\Button::create(dgettext('garudaplugin', 'Zuordnungen importieren'), 'import') ?>
        </footer>
    </form>
<?php endif;
