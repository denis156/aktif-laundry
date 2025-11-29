<div class="fab mb-6 mr-4">
  <!-- a focusable div with tabindex is necessary to work on all browsers. role="button" is necessary for accessibility -->
  <div tabindex="0" role="button" data-tip="Klik Aku" class="btn btn-xl btn-circle btn-secondary text-sm tooltip tooltip-secondary tooltip-open animate-bounce">
    <x-icon name="iconpark.press-o"/>
</div>

  <!-- close button should not be focusable so it can close the FAB when clicked. It's just a visual placeholder -->
  <div class="fab-close text-error font-bold">
    Tutup <span class="btn btn-circle btn-xl btn-error"><x-icon name="iconpark.handlex-o"/></span>
  </div>

  <!-- buttons that show up when FAB is open -->
  <x-button label="Chat Admin" icon="iconpark.communication-o" class="btn-md btn-success"/>
  <x-button label="Download Aplikasi" icon="iconpark.downloadfour-o" class="btn-md btn-primary"/>
</div>
