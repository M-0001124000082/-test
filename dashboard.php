<?php
session_start();

// السماح فقط للادمن
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "admin") {
    header("Location: index.html");
    exit();
}

// الاتصال بقاعدة البيانات
$servername = "localhost";
$username = "root";  
$password = "";      
$dbname = "users_db"; 

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

// جلب الطلاب
$students = $conn->query("SELECT id, name, email, grade FROM students");

// جلب الدروس
$lessons = $conn->query("SELECT id, title, grade FROM lessons");

// جلب الاعلانات
$announcements = $conn->query("SELECT id, title, content FROM announcements");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>لوحة تحكم الإدمن</title>
  <!-- أيقونات -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkfQZ7T4qVh8C+z2eVvZ1G1Y7bQF8Y2Z9M2vGZl9zH3mB5wM1xjFQZb/w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <style>
    :root{
      --bg: #0b1220;
      --panel: rgba(255,255,255,0.06);
      --panel-2: rgba(255,255,255,0.08);
      --stroke: rgba(255,255,255,0.12);
      --txt: #e9eefc;
      --muted:#a8b3cf;
      --primary:#6ea8ff;
      --primary-2:#5b9bff;
      --accent:#ffae42;
      --ok:#22c55e;
      --warn:#f59e0b;
      --danger:#ef4444;
      --gradient: linear-gradient(135deg,#2b3a67 0%, #14213d 50%, #0b1220 100%);
      --glass-blur: saturate(180%) blur(10px);
      --radius: 18px;
      --radius-lg: 24px;
      --shadow: 0 12px 30px rgba(0,0,0,.35), inset 0 1px 0 rgba(255,255,255,.06);
    }
    *{box-sizing:border-box}
    html,body{height:100%}
    body{
      margin:0; background: var(--gradient); color: var(--txt);
      font-family: 'Cairo', system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial;
      display:flex; gap:18px; padding:18px;
    }
    /* Sidebar */
    .sidebar{
      width:290px; min-width:260px; background: var(--panel);
      border:1px solid var(--stroke); border-radius: var(--radius-lg);
      backdrop-filter: var(--glass-blur); -webkit-backdrop-filter: var(--glass-blur);
      box-shadow: var(--shadow); padding:18px; display:flex; flex-direction:column; gap:14px;
    }
    .brand{display:flex; align-items:center; gap:12px; padding:12px; border-radius:14px; background: var(--panel-2); border:1px solid var(--stroke)}
    .brand i{font-size:24px; color: var(--accent)}
    .brand h1{font-size:20px; margin:0; letter-spacing:.2px}
    .nav{display:flex; flex-direction:column; gap:6px; margin-top:6px}
    .nav a{
      display:flex; align-items:center; gap:12px; padding:12px 14px; text-decoration:none; color:var(--txt);
      border:1px solid transparent; border-radius:12px; transition:.25s ease;
    }
    .nav a:hover{background: var(--panel-2); border-color: var(--stroke)}
    .nav a.active{background:linear-gradient(135deg, rgba(110,168,255,.2), rgba(110,168,255,.06)); border-color: rgba(110,168,255,.35)}
    .spacer{flex:1}
    .quick-stats{display:grid; grid-template-columns:1fr 1fr; gap:10px}
    .qcard{background:var(--panel); border:1px solid var(--stroke); border-radius:14px; padding:12px; text-align:center}
    .qcard .val{font-size:20px; font-weight:800}
    .qcard .lbl{font-size:12px; color:var(--muted)}

    /* Main */
    .main{flex:1; display:flex; flex-direction:column; gap:18px}
    .topbar{
      display:flex; align-items:center; gap:12px; background: var(--panel); border:1px solid var(--stroke);
      border-radius: var(--radius-lg); padding:12px 14px; backdrop-filter: var(--glass-blur); box-shadow: var(--shadow);
    }
    .search{flex:1; display:flex; align-items:center; gap:10px; background: var(--panel-2); border:1px solid var(--stroke);
      padding:8px 12px; border-radius:12px}
    .search input{flex:1; background:transparent; border:none; color:var(--txt); outline: none; font-size:14px}
    .actions{display:flex; gap:8px}
    .btn{
      padding:10px 14px; border-radius:12px; border:1px solid var(--stroke); background: var(--panel-2); color:var(--txt);
      cursor:pointer; font-weight:700; transition:.25s; display:inline-flex; align-items:center; gap:8px
    }
    .btn:hover{transform: translateY(-1px); border-color: var(--primary)}
    .btn.primary{background: linear-gradient(135deg, #6ea8ff33, #6ea8ff1a); border-color: rgba(110,168,255,.5)}
    .btn.accent{background: linear-gradient(135deg, #ffae4229, #ffae4215); border-color: rgba(255,174,66,.5)}

    /* Grade Switcher */
    .grade-switch{
      display:flex; gap:10px; flex-wrap:wrap
    }
    .pill{
      padding:9px 14px; border-radius:999px; border:1px solid var(--stroke); background: var(--panel-2); cursor:pointer; font-weight:800;
      color:var(--muted); transition:.2s; user-select:none
    }
    .pill.active{color:var(--txt); background: linear-gradient(135deg, #6ea8ff2b, #6ea8ff10); border-color: rgba(110,168,255,.45)}

    /* Cards */
    .grid{display:grid; grid-template-columns: repeat(12, 1fr); gap:14px}
    .card{
      grid-column: span 12; background: var(--panel); border:1px solid var(--stroke); border-radius: var(--radius);
      padding:16px; box-shadow: var(--shadow)
    }
    .card h3{margin:0 0 8px; font-size:18px}
    .muted{color:var(--muted); font-size:13px}

    .stats{display:grid; grid-template-columns: repeat(4, 1fr); gap:12px}
    .stat{
      background: var(--panel-2); border:1px solid var(--stroke); border-radius: 14px; padding:14px; display:flex; align-items:center; gap:12px
    }
    .stat i{font-size:22px}
    .stat .kpi{font-weight:900; font-size:20px}

    .tools{display:grid; grid-template-columns: repeat(3, 1fr); gap:12px}
    .tool{
      background:linear-gradient(180deg, rgba(255,255,255,.04), rgba(255,255,255,.02)); border:1px solid var(--stroke); border-radius:14px; padding:14px; display:flex; flex-direction:column; gap:8px
    }
    .tool .tool-actions{display:flex; gap:8px; flex-wrap:wrap}

    /* Table */
    table{width:100%; border-collapse: collapse; overflow: hidden; border-radius:14px; border:1px solid var(--stroke)}
    thead th{background: rgba(255,255,255,.05); font-size:12px; text-align:right; padding:12px}
    tbody td{border-top:1px solid var(--stroke); padding:12px}
    tr:hover td{background: rgba(255,255,255,.03)}

    /* Modal */
    .modal-backdrop{position:fixed; inset:0; background:rgba(0,0,0,.5); display:none; align-items:center; justify-content:center; padding:18px; z-index:50}
    .modal{width:100%; max-width:560px; background: var(--panel); border:1px solid var(--stroke); border-radius:18px; padding:18px; box-shadow: var(--shadow)}
    .modal h4{margin:0 0 10px}
    .modal .row{display:flex; gap:10px}
    .modal input, .modal select, .modal textarea{
      width:100%; background: var(--panel-2); border:1px solid var(--stroke); border-radius:12px; padding:10px; color:var(--txt)
    }

    /* Footer */
    .footer{display:flex; align-items:center; justify-content:space-between; font-size:12px; color:var(--muted)}

    /* Responsive */
    @media (max-width:1200px){
      .tools{grid-template-columns: repeat(2, 1fr)}
      .stats{grid-template-columns: repeat(2, 1fr)}
    }
    @media (max-width:900px){
      body{flex-direction:column}
      .sidebar{width:100%; min-width:unset}
      .grid{grid-template-columns: repeat(6, 1fr)}
    }
    @media (max-width:640px){
      .tools{grid-template-columns: 1fr}
      .stats{grid-template-columns: 1fr}
    }


    /* تحسينات إضافية للاستجابة */

/* للأجهزة الصغيرة جداً (موبايل أقل من 480px) */
@media (max-width: 480px) {
  body {
    padding: 10px;
  }

  .sidebar {
    position: fixed;
    top: 0; right: -100%;
    height: 100%;
    width: 80%;
    max-width: 280px;
    z-index: 100;
    transition: right 0.3s ease;
  }
  .sidebar.active {
    right: 0;
  }

  .brand h1 {
    font-size: 16px;
  }
  .brand .muted {
    font-size: 11px;
  }

  .topbar {
    flex-wrap: wrap;
  }
  .search {
    width: 100%;
    margin-bottom: 10px;
  }
  .grade-switch {
    width: 100%;
    justify-content: center;
  }
  .actions {
    width: 100%;
    justify-content: space-around;
  }

  .grid {
    grid-template-columns: 1fr !important;
  }

  .tools {
    grid-template-columns: 1fr !important;
  }

  table {
    font-size: 12px;
  }
  table th, table td {
    padding: 8px;
  }

  .btn {
    font-size: 12px;
    padding: 8px 10px;
  }
}

/* للأجهزة المتوسطة (تابلت أقل من 768px) */
@media (max-width: 768px) {
  body {
    flex-direction: column;
  }
  .sidebar {
    width: 100%;
    position: relative;
  }
  .grid {
    grid-template-columns: 1fr;
  }
  .tools {
    grid-template-columns: 1fr;
  }
  .stats {
    grid-template-columns: 1fr 1fr;
  }
}

/* للأجهزة المتوسطة - الكبيرة (شاشات أقل من 1024px) */
@media (max-width: 1024px) {
  .grid {
    grid-template-columns: repeat(6, 1fr);
  }
  .tools {
    grid-template-columns: 1fr 1fr;
  }
}

  </style>
</head>
<body>
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="brand">
      <i class="fa-solid fa-graduation-cap"></i>
      <div>
        <h1>لوحة تحكم حمادة غازي</h1>
        <div class="muted">إدارة الصفوف: أولى - ثانية - ثالثة</div>
      </div>
    </div>

    <nav class="nav">
      <a class="active" href="#"><i class="fa-solid fa-gauge"></i> الرئيسية</a>
      <a href="#" onclick="openModal('lesson')"><i class="fa-solid fa-circle-plus"></i> إضافة درس</a>
      <a href="#" onclick="openModal('upload')"><i class="fa-solid fa-cloud-arrow-up"></i> رفع ملف</a>
      <a href="#" onclick="openModal('exam')"><i class="fa-solid fa-file-circle-check"></i> إنشاء امتحان</a>
      <a href="#" onclick="openModal('announce')"><i class="fa-solid fa-bullhorn"></i> إعلان عام</a>
    </nav>

    <div class="spacer"></div>

    <div class="quick-stats">
      <div class="qcard">
        <div class="val" id="qs-students">0</div>
        <div class="lbl">طلاب مسجلون</div>
      </div>
      <div class="qcard">
        <div class="val" id="qs-lessons">0</div>
        <div class="lbl">دروس منشورة</div>
      </div>
      <div class="qcard">
        <div class="val" id="qs-files">0</div>
        <div class="lbl">ملفات مرفوعة</div>
      </div>
      <div class="qcard">
        <div class="val" id="qs-exams">0</div>
        <div class="lbl">امتحانات</div>
      </div>
    </div>
  </aside>

  <!-- Main -->
  <main class="main">
    <div class="topbar">
      <div class="search"><i class="fa-solid fa-magnifying-glass"></i><input id="search" placeholder="ابحث في الدروس، الملفات أو الطلاب" /></div>
      <div class="grade-switch" id="gradeSwitch">
        <div class="pill active" data-grade="first">الأول الثانوي</div>
        <div class="pill" data-grade="second">الثاني الثانوي</div>
        <div class="pill" data-grade="third">الثالث الثانوي</div>
      </div>
      <div class="actions">
        <button class="btn accent" onclick="openModal('announce')"><i class="fa-solid fa-bullhorn"></i> إعلان</button>
        <button class="btn primary" onclick="openModal('lesson')"><i class="fa-solid fa-plus"></i> درس جديد</button>
      </div>
    </div>

    <!-- KPIs -->
    <section class="card">
      <h3>نظرة سريعة</h3>
      <div class="stats" id="kpis">
        <div class="stat"><i class="fa-solid fa-user-graduate"></i><div><div class="kpi" id="kpi-students">—</div><div class="muted">طلاب في الصف الحالي</div></div></div>
        <div class="stat"><i class="fa-solid fa-chalkboard"></i><div><div class="kpi" id="kpi-lessons">—</div><div class="muted">دروس هذا الشهر</div></div></div>
        <div class="stat"><i class="fa-solid fa-cloud-arrow-up"></i><div><div class="kpi" id="kpi-files">—</div><div class="muted">ملفات مرفوعة</div></div></div>
        <div class="stat"><i class="fa-solid fa-file-pen"></i><div><div class="kpi" id="kpi-exams">—</div><div class="muted">اختبارات مجدولة</div></div></div>
      </div>
    </section>

    <!-- Tools -->
    <section class="grid">
      <div class="card" style="grid-column: span 7;">
        <h3>إدارة الدروس</h3>
        <div class="muted">أضف، عدّل أو احذف دروس الصف المحدد</div>
        <div class="tools" id="lessonsList">
          <!-- دروس ديناميكية -->
        </div>
      </div>

      <div class="card" style="grid-column: span 5;">
        <h3>أحدث الطلاب</h3>
        <div class="muted">آخر طلبات التسجيل في الصف المحدد</div>
        <div style="margin-top:10px; overflow:auto">
          <table>
            <thead>
              <tr><th>الطالب</th><th>الهاتف</th><th>الحالة</th><th>إجراءات</th></tr>
            </thead>
            <tbody id="studentsTable">
              <!-- صفوف ديناميكية -->
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- Activity -->
    <section class="card">
      <h3>النشاط الأخير</h3>
      <div class="muted">سجل آخر التعديلات والرفع والامتحانات</div>
      <ul id="activity" style="list-style:none; padding:0; margin-top:10px; display:grid; gap:8px">
        <!-- عناصر ديناميكية -->
      </ul>
      <div class="footer"><span>© منصة القمة — لوحة تحكم الإدمن</span><span>ثيم: زجاجي • احترافي</span></div>
    </section>
  </main>

  <!-- Modals -->
  <div class="modal-backdrop" id="modal">
    <div class="modal">
      <h4 id="modalTitle">—</h4>
      <div id="modalBody"></div>
      <div style="margin-top:12px; display:flex; gap:8px; justify-content:flex-end">
        <button class="btn" onclick="closeModal()">إلغاء</button>
        <button class="btn primary" onclick="submitModal()"><i class="fa-solid fa-floppy-disk"></i> حفظ</button>
      </div>
    </div>
  </div>

  <script>
    // بيانات تجريبية لكل صف
    const data = {
      first: {
        kpis: { students: 124, lessons: 18, files: 57, exams: 2 },
        lessons: [
          {title:'النحو — المبتدأ والخبر', desc:'شرح + أمثلة + واجب', files:3},
          {title:'البلاغة — التشبيه', desc:'ملف PDF + فيديو', files:2},
          {title:'الأدب — العصر الجاهلي', desc:'مخطط ذهني', files:1},
        ],
        students: [
          {name:'أحمد خالد', phone:'01012345678', status:'قيد المراجعة'},
          {name:'منة محمد', phone:'01122334455', status:'مقبول'},
          {name:'كريم علي', phone:'01277665544', status:'مرفوض'},
        ],
        activity: [
          {icon:'fa-cloud-arrow-up', text:'رفع ملف شرح "التشبيه"'},
          {icon:'fa-circle-plus', text:'إضافة درس جديد في النحو'},
          {icon:'fa-file-circle-check', text:'جدولة اختبار قصير يوم الجمعة'},
        ]
      },
      second: {
        kpis: { students: 98, lessons: 14, files: 33, exams: 1 },
        lessons: [
          {title:'النصوص — المدرسة الواقعية', desc:'ملخص + خريطة مفاهيم', files:2},
          {title:'القواعد — الممنوع من الصرف', desc:'أمثلة وتمارين', files:4}
        ],
        students: [
          {name:'سلمى عادل', phone:'01066778822', status:'مقبول'},
          {name:'مصطفى رامي', phone:'01090909090', status:'قيد المراجعة'}
        ],
        activity: [
          {icon:'fa-bullhorn', text:'إعلان مراجعة شاملة يوم الأحد'},
          {icon:'fa-cloud-arrow-up', text:'رفع بنك أسئلة البلاغة'}
        ]
      },
      third: {
        kpis: { students: 141, lessons: 21, files: 64, exams: 3 },
        lessons: [
          {title:'النحو — كان وأخواتها', desc:'شرح + تدريبات', files:5},
          {title:'التعبير — المقال', desc:'ورقة عمل', files:1},
          {title:'نصوص — مدرسة أبولو', desc:'فيديو + PDF', files:3}
        ],
        students: [
          {name:'أسماء شريف', phone:'01111222333', status:'مقبول'},
          {name:'زياد طارق', phone:'01555555555', status:'قيد المراجعة'},
          {name:'مروان عماد', phone:'01033334444', status:'مقبول'}
        ],
        activity: [
          {icon:'fa-file-pen', text:'تعديل امتحان تجريبي نهائي'},
          {icon:'fa-cloud-arrow-up', text:'رفع ملخص كان وأخواتها'},
          {icon:'fa-circle-plus', text:'إضافة درس في النصوص'}
        ]
      }
    };

    let currentGrade = 'first';

    const el = (s) => document.querySelector(s);
    const els = (s) => [...document.querySelectorAll(s)];

    function render(){
      const g = data[currentGrade];
      // KPIs
      el('#qs-students').textContent = total(g.kpis.students);
      el('#qs-lessons').textContent = total(g.kpis.lessons);
      el('#qs-files').textContent = total(g.kpis.files);
      el('#qs-exams').textContent = total(g.kpis.exams);
      el('#kpi-students').textContent = g.kpis.students;
      el('#kpi-lessons').textContent = g.kpis.lessons;
      el('#kpi-files').textContent = g.kpis.files;
      el('#kpi-exams').textContent = g.kpis.exams;

      // Lessons
      const lessons = g.lessons.map((l, i)=>{
        return `
          <div class="tool">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:10px">
              <div style="display:flex; gap:10px; align-items:center">
                <i class="fa-solid fa-chalkboard"></i>
                <div>
                  <div style="font-weight:800">${l.title}</div>
                  <div class="muted">${l.desc}</div>
                </div>
              </div>
              <span class="muted"><i class="fa-solid fa-paperclip"></i> ${l.files} ملفات</span>
            </div>
            <div class="tool-actions">
              <button class="btn" onclick="editLesson(${i})"><i class="fa-solid fa-pen"></i> تعديل</button>
              <button class="btn" onclick="duplicateLesson(${i})"><i class="fa-solid fa-copy"></i> تكرار</button>
              <button class="btn" onclick="deleteLesson(${i})"><i class="fa-solid fa-trash"></i> حذف</button>
            </div>
          </div>`
      }).join('');
      el('#lessonsList').innerHTML = lessons || `<div class="muted">لا توجد دروس بعد لهذا الصف.</div>`;

      // Students table
      el('#studentsTable').innerHTML = g.students.map((s,i)=>`
        <tr>
          <td>${s.name}</td>
          <td>${s.phone}</td>
          <td><span class="pill" style="padding:6px 10px; ${statusStyle(s.status)}">${s.status}</span></td>
          <td>
            <button class="btn" onclick="approveStudent(${i})"><i class="fa-solid fa-circle-check"></i> قبول</button>
            <button class="btn" onclick="rejectStudent(${i})"><i class="fa-solid fa-circle-xmark"></i> رفض</button>
          </td>
        </tr>
      `).join('');

      // Activity
      el('#activity').innerHTML = g.activity.map(a=>`
        <li style="display:flex; gap:10px; align-items:center">
          <i class="fa-solid ${a.icon}"></i>
          <span>${a.text}</span>
          <span class="muted" style="margin-inline-start:auto">الآن</span>
        </li>
      `).join('');
    }

    function total(n){ return new Intl.NumberFormat('ar-EG').format(n) }

    function statusStyle(st){
      if(st.includes('مقبول')) return 'background:#14321e; border-color:#1f5130; color:#8ee5a1';
      if(st.includes('مرفوض')) return 'background:#3b1212; border-color:#5a1c1c; color:#f3a7a7';
      return 'background:#2b2b12; border-color:#4d4d1a; color:#f5e38e';
    }

    // Grade switcher
    els('.pill').forEach(p=>p.addEventListener('click', ()=>{
      els('.pill').forEach(x=>x.classList.remove('active'));
      p.classList.add('active');
      currentGrade = p.dataset.grade;
      render();
    }));

    // Search (تصفية بسيطة على الدروس)
    el('#search').addEventListener('input', (e)=>{
      const q = e.target.value.trim();
      const g = data[currentGrade];
      const filtered = g.lessons.filter(l=> (l.title+l.desc).includes(q));
      el('#lessonsList').innerHTML = filtered.map((l, i)=>`
        <div class="tool">
          <div style="display:flex; align-items:center; justify-content:space-between; gap:10px">
            <div style="display:flex; gap:10px; align-items:center">
              <i class="fa-solid fa-chalkboard"></i>
              <div>
                <div style="font-weight:800">${l.title}</div>
                <div class="muted">${l.desc}</div>
              </div>
            </div>
            <span class="muted"><i class="fa-solid fa-paperclip"></i> ${l.files} ملفات</span>
          </div>
          <div class="tool-actions">
            <button class="btn"><i class="fa-solid fa-pen"></i> تعديل</button>
            <button class="btn"><i class="fa-solid fa-copy"></i> تكرار</button>
            <button class="btn"><i class="fa-solid fa-trash"></i> حذف</button>
          </div>
        </div>`).join('');
    });

    // Modal helpers
    const modalBackdrop = el('#modal');
    let activeModal = null;

    function openModal(type){
      activeModal = type;
      modalBackdrop.style.display = 'flex';
      const body = el('#modalBody');
      const title = el('#modalTitle');
      if(type==='lesson'){
        title.textContent = 'إضافة درس جديد';
        body.innerHTML = `
          <div class="row">
            <input id="f-title" placeholder="عنوان الدرس" />
          </div>
          <div class="row">
            <textarea id="f-desc" rows="3" placeholder="وصف مختصر"></textarea>
          </div>
          <div class="row">
            <select id="f-grade">
              <option value="first">الأول الثانوي</option>
              <option value="second">الثاني الثانوي</option>
              <option value="third">الثالث الثانوي</option>
            </select>
            <input id="f-files" placeholder="عدد الملفات (اختياري)" type="number" min="0" />
          </div>`;
      }
      if(type==='upload'){
        title.textContent = 'رفع ملف';
        body.innerHTML = `
          <div class="row">
            <input id="u-name" placeholder="اسم الملف" />
          </div>
          <div class="row">
            <select id="u-grade">
              <option value="first">الأول الثانوي</option>
              <option value="second">الثاني الثانوي</option>
              <option value="third">الثالث الثانوي</option>
            </select>
            <input id="u-url" placeholder="رابط Google Drive" />
          </div>`;
      }
      if(type==='exam'){
        title.textContent = 'إنشاء امتحان';
        body.innerHTML = `
          <div class="row">
            <input id="e-title" placeholder="عنوان الامتحان" />
          </div>
          <div class="row">
            <select id="e-grade">
              <option value="first">الأول الثانوي</option>
              <option value="second">الثاني الثانوي</option>
              <option value="third">الثالث الثانوي</option>
            </select>
            <input id="e-date" type="datetime-local" />
          </div>`;
      }
      if(type==='announce'){
        title.textContent = 'إعلان عام';
        body.innerHTML = `
          <div class="row">
            <textarea id="a-text" rows="4" placeholder="اكتب نص الإعلان..."></textarea>
          </div>`;
      }
    }
    function closeModal(){ modalBackdrop.style.display = 'none'; activeModal = null }
    function submitModal(){
      // هنا ربط الـ API/باك إند لاحقًا. حاليًا نحدّث الواجهة فقط كتجربة.
      if(activeModal==='lesson'){
        const t = el('#f-title').value.trim();
        const d = el('#f-desc').value.trim();
        const g = el('#f-grade').value;
        const f = parseInt(el('#f-files').value||'0',10);
        if(t){ data[g].lessons.unshift({title:t, desc:d||'—', files:isNaN(f)?0:f}); data[g].kpis.lessons++; render(); closeModal(); pushActivity(`إضافة درس جديد: ${t}`) }
      }
      if(activeModal==='upload'){
        const n = el('#u-name').value.trim();
        const g = el('#u-grade').value;
        const url = el('#u-url').value.trim();
        if(n){ data[g].kpis.files++; render(); closeModal(); pushActivity(`رفع ملف: ${n}${url?` → ${url}`:''}`) }
      }
      if(activeModal==='exam'){
        const t = el('#e-title').value.trim();
        const g = el('#e-grade').value;
        const dt = el('#e-date').value;
        if(t){ data[g].kpis.exams++; render(); closeModal(); pushActivity(`إنشاء امتحان: ${t} — ${dt||'بدون موعد'}`) }
      }
      if(activeModal==='announce'){
        const txt = el('#a-text').value.trim();
        if(txt){ pushActivity(`إعلان: ${txt}`); closeModal() }
      }
    }

    function pushActivity(text){
      data[currentGrade].activity.unshift({icon:'fa-bell', text});
      render();
    }
function toggleSidebar(){
  document.querySelector('.sidebar').classList.toggle('active');
}

    // Lesson actions
    function editLesson(i){ const l = data[currentGrade].lessons[i]; openModal('lesson'); setTimeout(()=>{ el('#f-title').value = l.title; el('#f-desc').value = l.desc; el('#f-grade').value = currentGrade; el('#f-files').value = l.files; }, 0); }
    function duplicateLesson(i){ const l = {...data[currentGrade].lessons[i]}; l.title += ' (نسخة)'; data[currentGrade].lessons.splice(i+1,0,l); data[currentGrade].kpis.lessons++; render(); pushActivity(`تكرار درس: ${l.title}`) }
    function deleteLesson(i){ if(confirm('حذف الدرس؟')){ data[currentGrade].lessons.splice(i,1); data[currentGrade].kpis.lessons = Math.max(0, data[currentGrade].kpis.lessons-1); render(); pushActivity('حذف درس') } }

    // Student actions
    function approveStudent(i){ data[currentGrade].students[i].status='مقبول'; render(); pushActivity('تم قبول طالب') }
    function rejectStudent(i){ data[currentGrade].students[i].status='مرفوض'; render(); pushActivity('تم رفض طالب') }

    // Close modal on outside click / ESC
    modalBackdrop.addEventListener('click', (e)=>{ if(e.target===modalBackdrop) closeModal() })
    window.addEventListener('keydown', (e)=>{ if(e.key==='Escape') closeModal() })

    // أول تحميل
    render();
  </script>
</body>
</html>






