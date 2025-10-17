@extends('layout.main')
@section('content')
<div class="">
  <div class="card mb-4">
    <h5 class="card-header">Form Generator Command</h5>
    <div class="card-body">
      <div class="mb-3">
        <label class="form-label">IP - Device</label>
        <select id="ip_select" class="form-select" name="ip_id" aria-label="Select IP">
           <option value="">Select IP</option>
           @foreach($ips as $ip)
             <option value="{{ $ip->id }}">{{ $ip->ip }}{{ isset($ip->device) ? ' - '.$ip->device : '' }}</option>
           @endforeach
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Service Command</label>
        <select id="command_select" class="form-select" name="command_id" aria-label="Select Command" disabled>
           <option value="">Select Service Command</option>
           {{-- options populated by JS when IP selected --}}
        </select>
      </div>

      <div class="mb-3">
        <button id="btn_generate" class="btn btn-primary" disabled data-bs-toggle="modal" data-bs-target="#generateModal">
          Generate
        </button>
      </div>
    </div>
  </div>
</div>

@php
  // prepare minimal commands payload: id, ip (fk), service_command, command
  $__commands_payload = collect($commands ?? [])->map(function($c){
      return [
        'id' => $c->id ?? null,
        'ip' => $c->ip ?? null,
        'service_command' => $c->service_command ?? null,
        'command' => $c->command ?? null,
        'description' => $c->description ?? null,
      ];
  })->values();
@endphp

<!-- Generate result modal -->
<div class="modal fade" id="generateModal" tabindex="-1" aria-labelledby="generateModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="generateModalLabel">Generated Command</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p><strong>IP:</strong> <span id="modal_ip_text">-</span></p>
        <p><strong>Service Command:</strong> <span id="modal_service_text">-</span></p>
        <p><strong>Description:</strong> <span id="modal_description_text">-</span></p>
        <hr>
        <pre id="modal_command_text" style="white-space:pre-wrap; background:#f8f9fa; padding:12px; border-radius:4px;">-</pre>
      </div>
      <div class="modal-footer">
        <button id="modal_copy" type="button" class="btn btn-outline-secondary">Copy</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const ipSelect = document.getElementById('ip_select');
  const cmdSelect = document.getElementById('command_select');
  const btnGenerate = document.getElementById('btn_generate');

  const commands = @json($__commands_payload);

  // debug quick check
  console.log('commands payload:', commands);

  // init Select2 with custom matcher if Select2 is loaded
  if (window.jQuery && typeof jQuery.fn.select2 !== 'undefined') {
    (function($){
      function matchStart(params, data) {
        // If there are no search terms, return all of the data
        if ($.trim(params.term) === '') {
          return data;
        }

        // If this item has children, filter them
        if (typeof data.children !== 'undefined') {
          var filteredChildren = [];
          $.each(data.children, function (idx, child) {
            if (child.text.toUpperCase().indexOf(params.term.toUpperCase()) == 0) {
              filteredChildren.push(child);
            }
          });
          if (filteredChildren.length) {
            var modifiedData = $.extend({}, data, true);
            modifiedData.children = filteredChildren;
            return modifiedData;
          }
          return null;
        }

        // For single (leaf) options, match against its text
        if (typeof data.text === 'string' && data.text.toUpperCase().indexOf(params.term.toUpperCase()) === 0) {
          return data;
        }

        // Return `null` if the term should not be displayed
        return null;
      }

      $('#ip_select').select2({
        width: '100%',
        placeholder: 'Select IP',
        allowClear: true,
        matcher: matchStart,
        minimumResultsForSearch: 0,
        dropdownParent: $(document.body)
      });

      // also listen with jQuery (Select2-friendly)
      $('#ip_select').on('change', function() {
        console.log('ip_select changed ->', $(this).val());
        populateCommandsForIp($(this).val());
      });

      $('#command_select').select2({
        width: '100%',
        placeholder: 'Select Service Command',
        allowClear: true,
        minimumResultsForSearch: 0,
        dropdownParent: $(document.body)
      });
    })(jQuery);
  }

  function clearCommands() {
    // prefer jQuery when available so Select2 UI ikut berubah
    if (window.jQuery && $(cmdSelect).length) {
      $(cmdSelect).empty();
      $(cmdSelect).append($('<option>', { value: '', text: 'Select Service Command', disabled: true, selected: true }));
      $(cmdSelect).prop('disabled', true);
      try { $(cmdSelect).trigger('change.select2'); } catch(e){}
    } else {
      cmdSelect.innerHTML = '';
      const opt = document.createElement('option');
      opt.value = '';
      opt.text = 'Select Service Command';
      opt.disabled = true;
      opt.selected = true;
      cmdSelect.appendChild(opt);
      cmdSelect.disabled = true;
    }
    updateGenerateButton();
  }

  function populateCommandsForIp(ipId) {
    clearCommands();
    if (!ipId) return;

    const filtered = commands.filter(c => String(c.ip) === String(ipId) && (c.service_command || c.command));
    if (filtered.length === 0) {
      if (window.jQuery && $(cmdSelect).length) {
        $(cmdSelect).append($('<option>', { value: '', text: 'No Service Command for selected IP', disabled: true }));
        $(cmdSelect).prop('disabled', true);
        $(cmdSelect).trigger('change.select2');
      } else {
        const noOpt = document.createElement('option');
        noOpt.value = '';
        noOpt.text = 'No Service Command for selected IP';
        noOpt.disabled = true;
        cmdSelect.appendChild(noOpt);
        cmdSelect.disabled = true;
      }
      updateGenerateButton();
      return;
    }

    // add options via jQuery so Select2 knows about them
    if (window.jQuery && $(cmdSelect).length) {
      filtered.forEach(c => {
        const text = c.service_command ? c.service_command : (c.command ? c.command.substring(0,80) + '...' : '—');
        const $opt = $('<option>', { value: c.id, text: text });
        $(cmdSelect).append($opt);
      });
      $(cmdSelect).prop('disabled', false);
      $(cmdSelect).trigger('change.select2');
    } else {
      filtered.forEach(c => {
        const o = document.createElement('option');
        o.value = c.id;
        o.text = c.service_command ? c.service_command : (c.command ? c.command.substring(0,80) + '...' : '—');
        cmdSelect.appendChild(o);
      });
      cmdSelect.disabled = false;
    }
    updateGenerateButton();
  }

  function updateGenerateButton() {
    // read values via jQuery when Select2 is present
    const ipVal = (window.jQuery && $('#ip_select').length) ? $('#ip_select').val() : ipSelect.value;
    const cmdVal = (window.jQuery && $('#command_select').length) ? $('#command_select').val() : cmdSelect.value;
    btnGenerate.disabled = !(ipVal && cmdVal);
  }

  // ensure Select2 change handlers also call updateGenerateButton
  if (window.jQuery && typeof jQuery.fn.select2 !== 'undefined') {
    (function($){
      $('#ip_select').on('change', function() {
        populateCommandsForIp($(this).val());
        updateGenerateButton();
      });
      $('#command_select').on('change', function(){ updateGenerateButton(); });
    })(jQuery);
  } else {
    ipSelect.addEventListener('change', function () { populateCommandsForIp(this.value); updateGenerateButton(); });
    cmdSelect.addEventListener('change', updateGenerateButton);
  }

  // modal population when Generate clicked
  btnGenerate.addEventListener('click', function () {
    const selectedIpText = ipSelect.options[ipSelect.selectedIndex].text || '-';
    const cmdId = cmdSelect.value;
    const cmdObj = commands.find(c => String(c.id) === String(cmdId)) || null;

    document.getElementById('modal_ip_text').textContent = selectedIpText;
    document.getElementById('modal_service_text').textContent = cmdObj ? (cmdObj.service_command ?? '-') : '-';
    document.getElementById('modal_description_text').textContent = cmdObj ? (cmdObj.description ?? '-') : '-';
    document.getElementById('modal_command_text').textContent = cmdObj ? (cmdObj.command ?? '-') : '-';
    // modal is triggered by data-bs-target, Bootstrap handles show
  });

  // copy button
  document.getElementById('modal_copy').addEventListener('click', function () {
    const txt = document.getElementById('modal_command_text').textContent || '';
    navigator.clipboard?.writeText(txt).then(()=> {
      this.textContent = 'Copied';
      setTimeout(()=> this.textContent = 'Copy', 1500);
    });
  });
});
</script>
@endsection