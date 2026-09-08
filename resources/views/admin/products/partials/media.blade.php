<section class="rounded-xl border bg-white p-6 shadow-sm space-y-6">
  <div class="flex flex-wrap items-center justify-between gap-2">
    <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Media & files</h2>
    <a href="{{ route('admin.settings.storage') }}" class="text-xs text-emerald-700">Storage settings →</a>
  </div>

  <div class="rounded-lg border border-slate-100 p-4" data-media-field="thumbnail">
    <label class="text-sm font-medium">Thumbnail</label>
    <div class="mt-2 flex flex-wrap gap-2 text-xs">
      <button type="button" class="media-tab rounded-full border px-3 py-1" data-tab="url">External / Drive URL</button>
      <button type="button" class="media-tab rounded-full border px-3 py-1" data-tab="upload">Upload</button>
      <button type="button" class="media-tab rounded-full border px-3 py-1" data-tab="library">Library</button>
    </div>
    <div class="media-pane mt-3" data-pane="url">
      <input name="thumbnail_url" id="thumbnail_url" value="{{ old('thumbnail_url', $item->thumbnail_url) }}" class="w-full rounded-lg border px-3 py-2 text-sm" placeholder="https://… or Google Drive share link">
    </div>
    <div class="media-pane mt-3 hidden" data-pane="upload">
      <div class="flex flex-wrap items-end gap-2">
        <div class="flex-1 min-w-[140px]">
          <label class="text-xs">Disk</label>
          <select class="media-disk mt-1 w-full rounded-lg border px-2 py-2 text-sm">
            <option value="local">Local</option>
            <option value="s3">Amazon S3</option>
            <option value="backblaze">Backblaze B2</option>
            <option value="idrive">iDrive e2</option>
          </select>
        </div>
        <div class="flex-[2] min-w-[180px]">
          <label class="text-xs">File</label>
          <input type="file" accept="image/*" class="media-file mt-1 block w-full text-sm">
        </div>
        <button type="button" class="media-upload-btn rounded-lg bg-slate-800 px-3 py-2 text-sm font-medium text-white">Upload</button>
      </div>
      <p class="media-status mt-1 text-xs text-slate-500"></p>
    </div>
    <div class="media-pane mt-3 hidden" data-pane="library">
      <button type="button" class="media-pick-btn rounded-lg border px-3 py-2 text-sm">Pick from media library</button>
    </div>
    <img id="thumbnail_preview" src="{{ old('thumbnail_url', $item->thumbnail_url) }}" alt="" class="mt-3 h-24 rounded-lg border object-cover {{ old('thumbnail_url', $item->thumbnail_url) ? '' : 'hidden' }}">
  </div>

  <div class="rounded-lg border border-slate-100 p-4" data-media-field="gallery">
    <label class="text-sm font-medium">Screenshots</label>
    <p class="text-xs text-slate-500">One image URL per line (or upload / pick multiple).</p>
    <div class="mt-2 flex flex-wrap gap-2 text-xs">
      <button type="button" class="media-tab rounded-full border px-3 py-1" data-tab="url">External / Drive URLs</button>
      <button type="button" class="media-tab rounded-full border px-3 py-1" data-tab="upload">Upload</button>
      <button type="button" class="media-tab rounded-full border px-3 py-1" data-tab="library">Library</button>
    </div>
    <div class="media-pane mt-3" data-pane="url">
      <textarea name="gallery_text" id="gallery_text" rows="4" class="w-full rounded-lg border px-3 py-2 text-sm">{{ $galleryText }}</textarea>
    </div>
    <div class="media-pane mt-3 hidden" data-pane="upload">
      <div class="flex flex-wrap items-end gap-2">
        <div class="flex-1 min-w-[140px]">
          <label class="text-xs">Disk</label>
          <select class="media-disk mt-1 w-full rounded-lg border px-2 py-2 text-sm">
            <option value="local">Local</option>
            <option value="s3">Amazon S3</option>
            <option value="backblaze">Backblaze B2</option>
            <option value="idrive">iDrive e2</option>
          </select>
        </div>
        <div class="flex-[2] min-w-[180px]">
          <label class="text-xs">Images</label>
          <input type="file" accept="image/*" multiple class="media-file mt-1 block w-full text-sm">
        </div>
        <button type="button" class="media-upload-btn rounded-lg bg-slate-800 px-3 py-2 text-sm font-medium text-white">Upload</button>
      </div>
      <p class="media-status mt-1 text-xs text-slate-500"></p>
    </div>
    <div class="media-pane mt-3 hidden" data-pane="library">
      <button type="button" class="media-pick-btn rounded-lg border px-3 py-2 text-sm" data-multi="1">Add from media library</button>
    </div>
  </div>

  <div>
    <label class="text-sm">Live demo URL</label>
    <input name="demo_url" value="{{ old('demo_url', $item->demo_url) }}" class="mt-1 w-full rounded-lg border px-3 py-2 text-sm">
  </div>

  <div class="rounded-lg border border-slate-100 p-4" data-media-field="mainfile" id="download-bundle">
    <div class="flex flex-wrap items-start justify-between gap-2">
      <div>
        <label class="text-sm font-medium">Download files (bundle)</label>
        <p class="text-xs text-slate-500">Main product file plus optional addons / extra packages.</p>
      </div>
      <button type="button" id="add-download-row" class="rounded-lg border border-emerald-600 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-50">+ Add file / URL</button>
    </div>
    <div id="download-rows" class="mt-3 space-y-3"></div>
    <div class="mt-4 rounded-lg border border-dashed border-slate-200 bg-slate-50 p-3">
      <p class="text-xs font-medium text-slate-600">Quick add via upload or library</p>
      <div class="mt-2 flex flex-wrap gap-2 text-xs">
        <button type="button" class="media-tab rounded-full border px-3 py-1 bg-emerald-50 border-emerald-500" data-tab="upload">Upload</button>
        <button type="button" class="media-tab rounded-full border px-3 py-1" data-tab="library">Library</button>
      </div>
      <div class="media-pane mt-3" data-pane="upload">
        <div class="flex flex-wrap items-end gap-2">
          <div class="min-w-[120px]">
            <label class="text-xs">Type</label>
            <select id="bundle-upload-type" class="mt-1 w-full rounded-lg border px-2 py-2 text-sm">
              <option value="main">Main</option>
              <option value="addon">Addon</option>
              <option value="extra">Extra</option>
            </select>
          </div>
          <div class="flex-1 min-w-[140px]">
            <label class="text-xs">Disk</label>
            <select class="media-disk mt-1 w-full rounded-lg border px-2 py-2 text-sm">
              <option value="local">Local</option>
              <option value="s3">Amazon S3</option>
              <option value="backblaze">Backblaze B2</option>
              <option value="idrive">iDrive e2</option>
            </select>
          </div>
          <div class="flex-[2] min-w-[160px]">
            <label class="text-xs">File(s)</label>
            <input type="file" multiple class="media-file mt-1 block w-full text-sm">
          </div>
          <button type="button" class="media-upload-btn rounded-lg bg-slate-800 px-3 py-2 text-sm font-medium text-white">Upload & add</button>
        </div>
        <p class="media-status mt-1 text-xs text-slate-500"></p>
      </div>
      <div class="media-pane mt-3 hidden" data-pane="library">
        <button type="button" class="media-pick-btn rounded-lg border px-3 py-2 text-sm" data-multi="1">Add from media library</button>
      </div>
    </div>
  </div>
</section>
