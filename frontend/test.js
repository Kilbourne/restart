var fs          = require('fs');
var path        = require('path');
var exec        = require('sync-exec');
var fontfacegen = require('fontfacegen');
var ttf2eot=require('ttf2eot');

var source = 'asset/fonts/';
var dest   = 'asset/fonts/';
var fonts  = fs.readdirSync(source);

exec('rm -rf ' + dest);

for (var i = fonts.length - 1; i >= 0; i--) {
    var font = fonts[i];
    var extension = path.extname(font);
    var fontname = path.basename(font, extension);

    // Test with embedded ttf
    if (extension == '.ttf' ) {
    	var so=path.join(source, font),so2=path.join(source, fontname,'.eot');

        ttf2eot(so,so2);

    }
};