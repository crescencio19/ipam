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

  function clearCommands() {
    cmdSelect.innerHTML = '';
    const opt = document.createElement('option');
    opt.value = '';
    opt.text = 'Select Service Command';
    opt.disabled = true;
    opt.selected = true;
    cmdSelect.appendChild(opt);
    cmdSelect.disabled = true;
    updateGenerateButton();
  }

  function populateCommandsForIp(ipId) {
    clearCommands();
    if (!ipId) return;

    const filtered = commands.filter(c => String(c.ip) === String(ipId) && (c.service_command || c.command));
    if (filtered.length === 0) {
      const noOpt = document.createElement('option');
      noOpt.value = '';
      noOpt.text = 'No Service Command for selected IP';
      noOpt.disabled = true;
      cmdSelect.appendChild(noOpt);
      cmdSelect.disabled = true;
      updateGenerateButton();
      return;
    }

    filtered.forEach(c => {
      const o = document.createElement('option');
      o.value = c.id;
      o.text = c.service_command ? c.service_command : (c.command ? c.command.substring(0,80) + '...' : '—');
      cmdSelect.appendChild(o);
    });
    cmdSelect.disabled = false;
    updateGenerateButton();
  }

  function updateGenerateButton() {
    btnGenerate.disabled = !(ipSelect.value && cmdSelect.value && !cmdSelect.disabled);
  }

  ipSelect.addEventListener('change', function () {
    populateCommandsForIp(this.value);
  });

  cmdSelect.addEventListener('change', updateGenerateButton);

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