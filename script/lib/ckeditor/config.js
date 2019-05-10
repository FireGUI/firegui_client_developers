/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
        config.toolbarGroups = [
            { name: 'mode', groups: ['tools', 'mode'] },
            { name: 'doctools', groups: ['doctools', 'document'] },
            { name: 'links' },
            { name: 'insert' },
            { name: 'forms' },
            '/',
            { name: 'editing', groups: [ 'find', 'selection'] },
            { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
            { name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ] },
            '/',
            { name: 'styles' },
            { name: 'colors' },
        ];

	// Remove some buttons, provided by the standard plugins, which we don't
	// need to have in the Standard(s) toolbar.
	//config.removeButtons = 'Save,NewPage,Preview,Print,Templates,Flash,Smiley,Iframe';
	config.removeButtons = 'Save,NewPage,Flash,Smiley';
        config.allowedContent = 'p u span h1 h2 h3 h4 h5 div hr img a button input em strong i table tbody thead tfoot th tr td dt dl dd address article b big br blockquote cite code font footer header strike small ul ol li sup sub center s[*]{*}(*)';
        config.extraAllowedContent = true;
};
