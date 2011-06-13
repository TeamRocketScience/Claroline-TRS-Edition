/*
    $Id: claroline.js 12976 2011-03-15 14:13:23Z zefredz $
    
    Main Claroline javascript library
 */

// Claroline namespace
var Claroline = {};

Claroline.version = '1.10 rev. $Revision: 12976 $';

Claroline.lang = {};

Claroline.getLang = function(langVar) {
    if ( Claroline.lang[langVar] ){
        return Claroline.lang[langVar];
    }
    else {
        return langVar;
    }
}

Claroline.json = {
    isResponse: function( response ) {
        return (typeof response.responseType != 'undefined') && (typeof response.responseBody != 'undefined');
    },
    isError: function( response ) {
        return Claroline.json.isResponse(response) && (response.responseType == 'error');
    },
    isSuccess: function( response ) {
        return Claroline.json.isResponse(response) && (response.responseType == 'success');
    },
    getResponseBody: function( response ) {
        return response.responseBody;
    },
    handleJsonError: function( response ) {
        error = Claroline.json.getResponseBody( response );
        
        var errStr = Claroline.getLang('[Error] ')+error.error;
        
        if ( error.errno ) {
            errStr += '('+error.errno+')';
        }
        
        if ( error.file ) {
            errStr += Claroline.getLang(' in ')+error.file;
            
            if ( error.line ) {
                errStr += Claroline.getLang(' at line ')+error.line;
            }
        }
        
        if ( error.trace ) {
            errStr += '\n\n'+error.trace;
        }
        
        alert( errStr );
    }
};

Claroline.spoil = function(item) {
    $(item).parents("div").children("div.spoilerContent").toggle();
    // change link display
    $(item).parents("div").children("a.reveal").toggleClass("showSpoiler");
    $(item).parents("div").children("a.reveal").toggleClass("hideSpoiler");

    return false;
};

$(document).ready( function (){
    // this is the core function of Claroline's jQuery implementation

    // ajax activity shower
    $("#loading").hide();

    $("#loading").ajaxStart(function(){
        $(this).show();
    });

    $("#loading").ajaxStop(function(){
        $(this).hide();
    });
    
    // multiple select
    $('.msadd').click(function() {
        return !$('#mslist1 option:selected').remove().appendTo('#mslist2');
    });
    
    $('.msremove').click(function() {
        return !$('#mslist2 option:selected').remove().appendTo('#mslist1');
    });
    
    $('.msform').submit(function() {
        $('#mslist1 option').each(function(i) {
            $(this).attr("selected", "selected");
        });
    });
});

// here should also come :

// - a standard confirmation box function
// - some object to set up standard environment vars ? (base url (module,...) courseId, userId, groupId, ...)
// - get_icon

function array_indexOf(arr,val)
{
    for ( var i = 0; i < arr.length; i++ )
    {
        if ( arr[i] == val )
        {
            return i;
        }
    }
    return -1;
}

function isDefined(a)
{
    return typeof a != 'undefined';
}

function isNull(a)
{
    return typeof a == 'object' && !a;
}

function dump(arr,level) {
    var dumped_text = "";
    if(!level) level = 0;

    //The padding given at the beginning of the line.
    var level_padding = "";
    for(var j=0;j<level+1;j++) level_padding += "    ";

    if(typeof(arr) == 'object') { //Array/Hashes/Objects
        for(var item in arr) {
            var value = arr[item];

            if(typeof(value) == 'object') { //If it is an array,
                dumped_text += level_padding + "'" + item + "' ...\n";
                dumped_text += dump(value,level+1);
            } else {
                dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
            }
        }
    } else { //Stings/Chars/Numbers etc.
        dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
    }
    return dumped_text;
}