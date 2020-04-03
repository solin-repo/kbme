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
 * @author Eugene Venter <eugene@catalyst.net.nz>
 * @author Alastair Munro <alastair.munro@totaralms.com>
 * @package totara
 * @subpackage facetoface
 */

M.totara_f2f_room = M.totara_f2f_room || {

    Y: null,
    // Optional php params and defaults defined here, args passed to init method
    // below will override these values
    config: {},
    // Public handler reference for the dialog
    totaraDialog_handler_preRequisite: null,

    /**
     * module initialisation method called by php js_init_call()
     *
     * @param object    YUI instance
     * @param object    configuration from PHP script
     */
    init: function(Y, config){
        var module = this;

        // Save a reference to the Y instance (all of its dependencies included)
        this.Y = Y;
        this.config = config;

        // Check jQuery dependency is available
        if (typeof $ === 'undefined') {
            throw new Error('M.totara_f2f_room.init()-> jQuery dependency required for this module to function.');
        }

        var url = M.cfg.wwwroot+'/mod/facetoface/room/ajax/';

        require(['mod_facetoface/addpdroom'], function (td_handler_addpdroom) {
            var handler = new td_handler_addpdroom();
            handler.setup_delete();

            var buttonsObj = {};
            buttonsObj[M.util.get_string('ok','moodle')] = function() { handler._save(); };
            buttonsObj[M.util.get_string('cancel','moodle')] = function() { handler._cancel(); };

            // Set the datetimeknown.
            M.totara_f2f_room.config.datetimeknown = $('select[name=datetimeknown]').val();

            totaraDialogs['addpdroom'] = new totaraDialog(
                    'addpdroom-dialog',
                    'show-addpdroom-dialog',
                    {
                        buttons: buttonsObj,
                        title: '<h2>' + M.util.get_string('chooseroom', 'facetoface') + M.totara_f2f_room.config.display_selected_item + '</h2>'
                    },
                    url + 'sessionrooms.php?sessionid=' + M.totara_f2f_room.config.sessionid +
                        '&facetofaceid=' + M.totara_f2f_room.config.facetofaceid +
                        '&datetimeknown=' + M.totara_f2f_room.config.datetimeknown,
                    handler
            );
        });

        $('select[name="datetimeknown"]').change(function() {
            if ($(this).val() == 1) {
                $('input[name="duration[number]"]').val('');
            }
        });

        // Clear pre-defined room selection and set room capacity if custom room is selected
        $('input[name="customroom"]').click(function() {
            if ($(this).is(':checked')) {
                clean_pdroom_data();
                $('#warn').remove();
                $('input[name="pdroomcapacity"]').val(0);
                $('input[name="croomcapacity"]').val($('input[name="capacity"]').val());
            }
        });

        clean_pdroom_data = function() {
            $('input[name="pdroomid"]').val(0);
            $('span#pdroom').html('');
            $('span#roomnote').html('');
        }

        is_pdroom_exceeded = function() {
            var pdroomcapacity = parseInt($('input[name="pdroomcapacity"]').val(), 10);
            var sessioncapacity = parseInt($('input[name="capacity"]').val(), 10);

            if ((sessioncapacity > pdroomcapacity) && (pdroomcapacity > 0) && ($.isNumeric($('input[name="capacity"]').val()))) {
                $('<div id=warn class="notice">' + M.util.get_string('pdroomcapacityexceeded', 'facetoface') + '</div>').insertBefore('input[name="capacity"]');
            }
        }

        // If pre-defined room capacity is exceeded by room capacity, a warning message will be shown
        $('input[name="capacity"]').bind('keyup blur', function() {
            $('#warn').remove();
            is_pdroom_exceeded();
        });
        // Show the warning message is the session was saved with pre-defined room capacity exceeded by room capacity
        $().ready(function() {
            is_pdroom_exceeded();
        });

        // Update session capacity if room capacity changes
        $('input[name="croomcapacity"]').bind('keyup cut paste', function() {
            var capacity = $(this);
            setTimeout(function() {
                $('input[name="capacity"]').val(capacity.val());
            });
        });
        // Update room capacity if session capacity changes
        $('input[name="capacity"]').bind('keyup cut paste', function() {
            if ($('input[name="customroom"]').is(':checked')) {
                var capacity = $(this);
                setTimeout(function() {
                    $('input[name="croomcapacity"]').val(capacity.val());
                });
            }
        });
    }
}

M.facetoface_datelinkage = M.facetoface_datelinkage || {
    previousstartvalues: {},

    getdate: function (dateelement) {
        return new Date(
            $('.fdate_time_selector select[name="' + dateelement + '[year]"]').val(),
            $('.fdate_time_selector select[name="' + dateelement + '[month]"]').val() - 1,
            $('.fdate_time_selector select[name="' + dateelement + '[day]"]').val(),
            $('.fdate_time_selector select[name="' + dateelement + '[hour]"]').val(),
            $('.fdate_time_selector select[name="' + dateelement + '[minute]"]').val()
        ).getTime() / 1000;
    },

    setdate: function (dateelement, timestamp) {
        date = new Date(timestamp * 1000);
        $('.fdate_time_selector select[name="' + dateelement + '[year]"]').val(date.getFullYear());
        $('.fdate_time_selector select[name="' + dateelement + '[month]"]').val(date.getMonth() + 1);
        $('.fdate_time_selector select[name="' + dateelement + '[day]"]').val(date.getDate());
        $('.fdate_time_selector select[name="' + dateelement + '[hour]"]').val(date.getHours());
        $('.fdate_time_selector select[name="' + dateelement + '[minute]"]').val(date.getMinutes());
    },

    init: function(){
        var repeatid = 0;
        $('input[name^="datedelete["]').each(function() {
            var element = 'timestart[' + repeatid + ']';
            var elementrepeatid = repeatid;

            M.facetoface_datelinkage.previousstartvalues[elementrepeatid] = M.facetoface_datelinkage.getdate(element);

            $('.fdate_time_selector select[name^="timestart[' + elementrepeatid + '"]').change(function() {
                newstartdate = M.facetoface_datelinkage.getdate(element);
                oldstartdate = M.facetoface_datelinkage.previousstartvalues[elementrepeatid]

                var finishelement = 'timefinish[' + elementrepeatid + ']';
                currentfinishdate = M.facetoface_datelinkage.getdate(finishelement);
                newfinishdate = currentfinishdate + (newstartdate - oldstartdate);
                M.facetoface_datelinkage.setdate(finishelement, newfinishdate);

                M.facetoface_datelinkage.previousstartvalues[elementrepeatid] = M.facetoface_datelinkage.getdate(element);
            });

            repeatid += 1;
        });
    }
}
