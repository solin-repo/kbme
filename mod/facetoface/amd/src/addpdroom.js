/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2010 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Brian Barnes <brian.barnes@totaralms.com>
 * @package totara
 * @subpackage facetoface
 */

define(['jquery', 'core/str', 'core/config'], function($, mdlstrings, cfg) {
    var totaraDialog_handler_addpdroom = function() {};
    totaraDialog_handler_addpdroom.prototype = new totaraDialog_handler_treeview_singleselect('pdroomid', 'pdroom');

    totaraDialog_handler_addpdroom.prototype.setup_delete = function() {

        this.deletable = true;
        var textel = $('#'+this.text_element_id);
        var idel = $('input[name='+this.value_element_name+']');
        var deletebutton = $('<span class="dialog-singleselect-deletable">');
        mdlstrings.get_string('delete', 'totara_core').done(function (string) {
            deletebutton.html(string);
        });
        var handler = this;

        // Setup handler
        deletebutton.click(function() {
            idel.val('');
            textel.removeClass('nonempty');
            textel.empty();
            $('input[name="pdroomid"]').val(0);
            $('span#roomnote').html('');
            $('input[name="capacity"]').val('10');
            $('input[name="pdroomcapacity"]').val(0);
            $('#warn').remove();
            handler.setup_delete();
        });

        if (textel.text().length) {
            textel.append(deletebutton);
        }
    };

    totaraDialog_handler_addpdroom.prototype._open = function() {
        var datecount = 0;
        var timeslots = [];
        $('input[name^="datedelete["]').each(function() {
            if (!$(this).is(':checked')) {
                var timeslot = {};
                var minute = $('.fdate_time_selector select[name="timestart['+datecount+'][minute]"]').val();
                if (minute == undefined) {
                    minute = "00";
                }
                timeslot.start =
                    $('.fdate_time_selector select[name="timestart['+datecount+'][year]"]').val() + '-' +
                    $('.fdate_time_selector select[name="timestart['+datecount+'][month]"]').val() + '-' +
                    $('.fdate_time_selector select[name="timestart['+datecount+'][day]"]').val() + ' ' +
                    $('.fdate_time_selector select[name="timestart['+datecount+'][hour]"]').val() + ':' +
                    minute + ' ' +
                    $('.fdate_time_selector select[name="timestart['+datecount+'][timezone]"]').val();

                minute = $('.fdate_time_selector select[name="timefinish['+datecount+'][minute]"]').val();
                if (minute == undefined) {
                    minute = "00";
                }
                timeslot.finish =
                    $('.fdate_time_selector select[name="timefinish['+datecount+'][year]"]').val() + '-' +
                    $('.fdate_time_selector select[name="timefinish['+datecount+'][month]"]').val() + '-' +
                    $('.fdate_time_selector select[name="timefinish['+datecount+'][day]"]').val() + ' ' +
                    $('.fdate_time_selector select[name="timefinish['+datecount+'][hour]"]').val() + ':' +
                    minute + ' ' +
                    $('.fdate_time_selector select[name="timefinish['+datecount+'][timezone]"]').val();

                timeslots.push(timeslot);
            }
            datecount += 1;
        });
        // Update the url to include the timestamps
        timeslots = JSON.stringify(timeslots);
        this._dialog.default_url = cfg.wwwroot+'/mod/facetoface/room/ajax/sessionrooms.php'+
            '?sessionid='+M.totara_f2f_room.config.sessionid+
            '&datetimeknown='+$('#id_datetimeknown').val()+
            '&timeslots='+timeslots+
            '&facetofaceid='+M.totara_f2f_room.config.facetofaceid;
    };

    totaraDialog_handler_addpdroom.prototype.first_load = function() {
        // Call parent function
        totaraDialog_handler_treeview_singleselect.prototype.first_load.call(this);
    };

    totaraDialog_handler_addpdroom.prototype.every_load = function() {
        // Call parent function
        totaraDialog_handler_treeview_singleselect.prototype.every_load.call(this);

        var selected_val = $('#treeview_selected_val_'+this._title).val();

        // Add footnote flag to all unclickable items, except the currently selected one
        $('span.unclickable').not('#item_'+selected_val).has('a').not('.hasfootnoteflag').each(function() {
            $(this).addClass('hasfootnoteflag');
        });
    };

    // Called as part of _save
    totaraDialog_handler_addpdroom.prototype.external_function = function() {
        // Set the chosen room capacity
        $.ajax({
            url: cfg.wwwroot+'/mod/facetoface/room/ajax/roomcap.php?id='+$('input[name="pdroomid"]').val(),
        type: 'GET',
        success: function(o) {
            if (o.length) {
                $('#warn').remove();
                $('input[name="capacity"]').val(o);
                $('input[name="pdroomcapacity"]').val(o);
            }
        }
        });
        // Set room note
        $.ajax({
            url: cfg.wwwroot+'/mod/facetoface/room/ajax/roomnote.php?id='+$('input[name="pdroomid"]').val(),
            type: 'GET',
            success: function(o) {
                if (o.length) {
                    o = '<br>' + o;
                }
                $('span#roomnote').html(o);
            },
            error: function() {
                $('span#roomnote').html('');  // remove all notes
            }
        });

        // Clear custom room
        $('input[name="customroom"]').prop("checked", false);
        // Disable custom room if pre-defined room is selected
        $('input[name="croomname"], input[name="croombuilding"], input[name="croomaddress"], input[name="croomcapacity"]').prop('disabled', true);
    };

    return totaraDialog_handler_addpdroom;
});
