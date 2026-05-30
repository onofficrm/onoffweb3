(function () {
  'use strict';

  function bindUploadForm() {
    var form = document.getElementById('onoffBuilderUploadForm');
    if (!form) {
      return;
    }

    form.addEventListener('submit', function (e) {
      var zip = document.getElementById('zip_file');
      var pid = document.getElementById('project_id');
      var pname = document.getElementById('project_name');

      if (pid && !/^[a-z0-9_-]{2,50}$/.test(pid.value.trim())) {
        e.preventDefault();
        alert('프로젝트 ID는 영문 소문자, 숫자, -, _ 만 2~50자로 입력하세요.');
        return;
      }

      if (pname && !pname.value.trim()) {
        e.preventDefault();
        alert('프로젝트 이름을 입력하세요.');
        return;
      }

      if (zip && zip.files && zip.files.length) {
        var name = zip.files[0].name.toLowerCase();
        if (name.slice(-4) !== '.zip') {
          e.preventDefault();
          alert('dist ZIP 파일(.zip)만 업로드할 수 있습니다.');
          return;
        }
      } else if (zip) {
        e.preventDefault();
        alert('ZIP 파일을 선택하세요.');
      }
    });
  }

  function bindDeleteForms() {
    var forms = document.querySelectorAll('.js-onoff-builder-delete-form');
    if (!forms || !forms.length) {
      return;
    }

    var defaultMessage =
      '이 프로젝트를 삭제할까요?\n\nimports 폴더와 등록 정보가 함께 삭제되며 복구할 수 없습니다.';

    Array.prototype.forEach.call(forms, function (form) {
      if (!form || form.tagName !== 'FORM') {
        return;
      }

      form.addEventListener('submit', function (e) {
        var raw = form.getAttribute('data-confirm') || defaultMessage;
        var message = raw.replace(/&#10;/g, '\n');
        if (!window.confirm(message)) {
          e.preventDefault();
        }
      });
    });
  }

  bindUploadForm();
  bindDeleteForms();
})();
