<?php 


// 2) Fetch all "present" attendances + event + committee role
$sql = "
  SELECT
    e.event_id,
    e.title             AS event_name,
    e.event_level,
    e.geographic_location,
    e.start_date,
    e.end_date,
    COALESCE(ec.role,'Participant') AS committee_role
  FROM attendance a
  JOIN events    e ON a.event_id = e.event_id
  LEFT JOIN eventcommittee ec
    ON ec.event_id = a.event_id
   AND ec.user_id  = a.user_id
  WHERE a.user_id     = ?
    AND a.status_attd = 'present'
  ORDER BY e.start_date DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$attendances = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// 3) Points map
$points_map = [
  'International'=>['Main committee'=>100,'Committee'=>70,'Participant'=>50],
  'National'     =>['Main committee'=> 80,'Committee'=>50,'Participant'=>40],
  'State'        =>['Main committee'=> 60,'Committee'=>40,'Participant'=>30],
  'District'     =>['Main committee'=> 40,'Committee'=>30,'Participant'=>15],
  'UMPSA'        =>['Main committee'=> 30,'Committee'=>20,'Participant'=> 5],
];

// Prepare an INSERT IGNORE so we don’t double-insert
$insertMerit = $conn->prepare("
  INSERT IGNORE INTO merit 
    (event_id, user_id, points, semester, academic_year) 
  VALUES (?,?,?,?,?)
");

$total_points        = 0;
$current_year_points = 0;
$current_year        = date('Y');
$events_data         = [];

foreach ($attendances as $r) {
  $role  = $r['committee_role'];
  $level = $r['event_level'];

  // 3a) lookup points
  $pts = $points_map[$level][$role]
      ?? $points_map[$level]['Participant']
      ?? 0;

  // 3b) derive academic_year & numeric semester
  $evDate = $r['start_date'];
  $acadYear = (int)date('Y', strtotime($evDate));
  $month    = (int)date('n', strtotime($evDate));
  if      ($month >=  2 && $month <=  6) $sem = 2;
  elseif  ($month >=  9 && $month <= 12) $sem = 1;
  else                                   $sem = 0; // or your “special” code

  // 4) persist into merit table
  $insertMerit->bind_param(
    "iiiis",
    $r['event_id'],
    $user_id,
    $pts,
    $sem,
    $acadYear
  );
  $insertMerit->execute();

  // 5) accumulate for summary
  $total_points += $pts;
  if ($acadYear === (int)$current_year) {
    $current_year_points += $pts;
  }
  $events_data[] = $r + [
    'role'          => $role,
    'points'        => $pts,
    'semester'      => $sem,
    'academic_year' => $acadYear
  ];
}

$insertMerit->close();

?>