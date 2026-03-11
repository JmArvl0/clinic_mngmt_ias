<?php
require_once 'config.php';
requireLogin();

header('Content-Type: application/json');
$db     = getDB();
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$module = $_POST['module'] ?? $_GET['module'] ?? '';

switch ($module) {
    case 'students':        handleStudents($action, $db);       break;
    case 'medical_records': handleMedicalRecords($action, $db); break;
    case 'consultations':   handleConsultations($action, $db);  break;
    case 'medicines':       handleMedicines($action, $db);      break;
    case 'dispensing':      handleDispensing($action, $db);     break;
    case 'clearances':      handleClearances($action, $db);     break;
    case 'incidents':       handleIncidents($action, $db);      break;
    case 'dashboard':       handleDashboard($db);               break;
    case 'users':           handleUsers($action, $db);          break;
    default: echo json_encode(['error' => 'Invalid module']);   break;
}

/* ── HELPERS ── */
function esc($db, $val) { return $db->real_escape_string($val ?? ''); }
function nullDate($val)  { return ($val === '' || $val === null) ? 'NULL' : "'$val'"; }

/* ── DASHBOARD ── */
function handleDashboard($db) {
    $out = [];
    $out['total_students']      = $db->query("SELECT COUNT(*) c FROM students")->fetch_assoc()['c'];
    $out['consultations_today'] = $db->query("SELECT COUNT(*) c FROM consultations WHERE DATE(visit_date)=CURDATE()")->fetch_assoc()['c'];
    $out['low_stock_medicines'] = $db->query("SELECT COUNT(*) c FROM medicines WHERE quantity_in_stock<=minimum_stock AND (expiry_date IS NULL OR expiry_date>=CURDATE())")->fetch_assoc()['c'];
    $out['pending_clearances']  = $db->query("SELECT COUNT(*) c FROM medical_clearances WHERE status='pending'")->fetch_assoc()['c'];
    $out['open_incidents']      = $db->query("SELECT COUNT(*) c FROM health_incidents WHERE status='open'")->fetch_assoc()['c'];
    $out['total_consultations'] = $db->query("SELECT COUNT(*) c FROM consultations")->fetch_assoc()['c'];

    $out['recent_consultations'] = [];
    $r = $db->query("SELECT c.id,s.full_name,s.student_id,c.visit_date,c.chief_complaint,c.status
                     FROM consultations c JOIN students s ON c.student_id=s.id
                     ORDER BY c.visit_date DESC LIMIT 5");
    while ($row = $r->fetch_assoc()) $out['recent_consultations'][] = $row;

    $out['monthly_data'] = [];
    $r = $db->query("SELECT MONTH(visit_date) month,COUNT(*) count FROM consultations
                     WHERE YEAR(visit_date)=YEAR(CURDATE()) GROUP BY MONTH(visit_date) ORDER BY month");
    while ($row = $r->fetch_assoc()) $out['monthly_data'][] = $row;

    echo json_encode($out);
}

/* ── STUDENTS ── */
function handleStudents($action, $db) {
    switch ($action) {
        case 'list':
            $s = '%'.esc($db,$_GET['search'] ?? '').'%';
            $r = $db->query("SELECT * FROM students WHERE full_name LIKE '$s' OR student_id LIKE '$s' ORDER BY full_name");
            $rows=[]; while($x=$r->fetch_assoc()) $rows[]=$x;
            echo json_encode($rows); break;

        case 'get':
            $id=intval($_GET['id']);
            $st=$db->prepare("SELECT * FROM students WHERE id=?");
            $st->bind_param('i',$id); $st->execute();
            echo json_encode($st->get_result()->fetch_assoc()); $st->close(); break;

        case 'save':
            $id  = intval($_POST['id'] ?? 0);
            $sid = esc($db,$_POST['student_id']??'');
            $fn  = esc($db,$_POST['full_name']??'');
            $dob = esc($db,$_POST['date_of_birth']??'');   $dob = $dob ? "'$dob'" : 'NULL';
            $gen = esc($db,$_POST['gender']??'');
            $crs = esc($db,$_POST['course']??'');
            $yr  = esc($db,$_POST['year_level']??'');
            $sec = esc($db,$_POST['section']??'');
            $con = esc($db,$_POST['contact_number']??'');
            $em  = esc($db,$_POST['email']??'');
            $adr = esc($db,$_POST['address']??'');
            $gn  = esc($db,$_POST['guardian_name']??'');
            $gc  = esc($db,$_POST['guardian_contact']??'');
            $bt  = esc($db,$_POST['blood_type']??'Unknown');

            if ($id>0) {
                $db->query("UPDATE students SET student_id='$sid',full_name='$fn',date_of_birth=$dob,
                            gender='$gen',course='$crs',year_level='$yr',section='$sec',
                            contact_number='$con',email='$em',address='$adr',
                            guardian_name='$gn',guardian_contact='$gc',blood_type='$bt' WHERE id=$id");
            } else {
                $db->query("INSERT INTO students(student_id,full_name,date_of_birth,gender,course,year_level,
                            section,contact_number,email,address,guardian_name,guardian_contact,blood_type)
                            VALUES('$sid','$fn',$dob,'$gen','$crs','$yr','$sec','$con','$em','$adr','$gn','$gc','$bt')");
            }
            echo $db->error ? json_encode(['error'=>$db->error]) : json_encode(['success'=>true,'id'=>$id>0?$id:$db->insert_id]);
            break;

        case 'delete':
            $id=intval($_POST['id']);
            $db->query("DELETE FROM students WHERE id=$id");
            echo json_encode(['success'=>true]); break;

        default: echo json_encode(['error'=>'Unknown action']);
    }
}

/* ── MEDICAL RECORDS ── */
function handleMedicalRecords($action, $db) {
    switch ($action) {
        case 'list':
            $sid  = intval($_GET['student_id'] ?? 0);
            $s    = '%'.esc($db,$_GET['search']??'').'%';
            $cond = $sid>0 ? "WHERE mr.student_id=$sid" : "WHERE (s.full_name LIKE '$s' OR s.student_id LIKE '$s')";
            $r = $db->query("SELECT mr.*,s.full_name,s.student_id as sid,u.full_name as recorded_by_name
                             FROM medical_records mr JOIN students s ON mr.student_id=s.id
                             LEFT JOIN users u ON mr.recorded_by=u.id $cond ORDER BY mr.record_date DESC");
            $rows=[]; while($x=$r->fetch_assoc()) $rows[]=$x;
            echo json_encode($rows); break;

        case 'get':
            $id=intval($_GET['id']);
            $st=$db->prepare("SELECT mr.*,s.full_name,s.student_id as sid FROM medical_records mr
                              JOIN students s ON mr.student_id=s.id WHERE mr.id=?");
            $st->bind_param('i',$id); $st->execute();
            echo json_encode($st->get_result()->fetch_assoc()); $st->close(); break;

        case 'save':
            $uid  = intval(currentUser()['id']);
            $id   = intval($_POST['id']??0);
            $sid  = intval($_POST['student_id']??0);
            $rd   = esc($db,$_POST['record_date']??date('Y-m-d'));
            $ht   = is_numeric($_POST['height_cm']??'') ? floatval($_POST['height_cm']) : 'NULL';
            $wt   = is_numeric($_POST['weight_kg']??'') ? floatval($_POST['weight_kg']) : 'NULL';
            $bp   = esc($db,$_POST['blood_pressure']??'');
            $pls  = is_numeric($_POST['pulse_rate']??'') ? intval($_POST['pulse_rate']) : 'NULL';
            $tmp  = is_numeric($_POST['temperature']??'') ? floatval($_POST['temperature']) : 'NULL';
            $vl   = esc($db,$_POST['vision_left']??'');
            $vr   = esc($db,$_POST['vision_right']??'');
            $al   = esc($db,$_POST['allergies']??'');
            $cc   = esc($db,$_POST['chronic_conditions']??'');
            $pi   = esc($db,$_POST['past_illnesses']??'');
            $sh   = esc($db,$_POST['surgical_history']??'');
            $fh   = esc($db,$_POST['family_medical_history']??'');
            $vac  = esc($db,$_POST['vaccination_records']??'');
            $nt   = esc($db,$_POST['medical_notes']??'');

            if ($id>0) {
                $db->query("UPDATE medical_records SET student_id=$sid,record_date='$rd',height_cm=$ht,weight_kg=$wt,
                            blood_pressure='$bp',pulse_rate=$pls,temperature=$tmp,vision_left='$vl',vision_right='$vr',
                            allergies='$al',chronic_conditions='$cc',past_illnesses='$pi',surgical_history='$sh',
                            family_medical_history='$fh',vaccination_records='$vac',medical_notes='$nt',recorded_by=$uid
                            WHERE id=$id");
            } else {
                $db->query("INSERT INTO medical_records(student_id,record_date,height_cm,weight_kg,blood_pressure,
                            pulse_rate,temperature,vision_left,vision_right,allergies,chronic_conditions,
                            past_illnesses,surgical_history,family_medical_history,vaccination_records,medical_notes,recorded_by)
                            VALUES($sid,'$rd',$ht,$wt,'$bp',$pls,$tmp,'$vl','$vr','$al','$cc','$pi','$sh','$fh','$vac','$nt',$uid)");
            }
            echo $db->error ? json_encode(['error'=>$db->error]) : json_encode(['success'=>true]);
            break;

        case 'delete':
            $id=intval($_POST['id']);
            $db->query("DELETE FROM medical_records WHERE id=$id");
            echo json_encode(['success'=>true]); break;

        default: echo json_encode(['error'=>'Unknown action']);
    }
}

/* ── CONSULTATIONS ── */
function handleConsultations($action, $db) {
    switch ($action) {
        case 'list':
            $s   = '%'.esc($db,$_GET['search']??'').'%';
            $fil = esc($db,$_GET['status']??'');
            $cnd = $fil ? "AND c.status='$fil'" : '';
            $r = $db->query("SELECT c.*,s.full_name,s.student_id as sid,u.full_name as staff_name
                             FROM consultations c JOIN students s ON c.student_id=s.id
                             LEFT JOIN users u ON c.attending_staff=u.id
                             WHERE (s.full_name LIKE '$s' OR s.student_id LIKE '$s') $cnd
                             ORDER BY c.visit_date DESC LIMIT 200");
            $rows=[]; while($x=$r->fetch_assoc()) $rows[]=$x;
            echo json_encode($rows); break;

        case 'get':
            $id=intval($_GET['id']);
            $st=$db->prepare("SELECT c.*,s.full_name,s.student_id as sid,s.course,s.year_level
                              FROM consultations c JOIN students s ON c.student_id=s.id WHERE c.id=?");
            $st->bind_param('i',$id); $st->execute();
            echo json_encode($st->get_result()->fetch_assoc()); $st->close(); break;

        case 'save':
            $uid = intval(currentUser()['id']);
            $id  = intval($_POST['id']??0);
            $sid = intval($_POST['student_id']??0);
            $vd  = esc($db,$_POST['visit_date']??date('Y-m-d H:i:s'));
            $cc  = esc($db,$_POST['chief_complaint']??'');
            $sym = esc($db,$_POST['symptoms']??'');
            $dx  = esc($db,$_POST['diagnosis']??'');
            $tx  = esc($db,$_POST['treatment_given']??'');
            $rx  = esc($db,$_POST['prescription']??'');
            $vs  = esc($db,$_POST['vital_signs']??'');
            $fu  = esc($db,$_POST['follow_up_date']??''); $fu = $fu ? "'$fu'" : 'NULL';
            $ref = esc($db,$_POST['referral']??'');
            $st  = esc($db,$_POST['status']??'completed');
            $nt  = esc($db,$_POST['notes']??'');

            if ($id>0) {
                $db->query("UPDATE consultations SET student_id=$sid,visit_date='$vd',chief_complaint='$cc',
                            symptoms='$sym',diagnosis='$dx',treatment_given='$tx',prescription='$rx',
                            vital_signs='$vs',follow_up_date=$fu,referral='$ref',status='$st',
                            notes='$nt',attending_staff=$uid WHERE id=$id");
            } else {
                $db->query("INSERT INTO consultations(student_id,visit_date,chief_complaint,symptoms,diagnosis,
                            treatment_given,prescription,vital_signs,follow_up_date,referral,status,notes,attending_staff)
                            VALUES($sid,'$vd','$cc','$sym','$dx','$tx','$rx','$vs',$fu,'$ref','$st','$nt',$uid)");
            }
            echo $db->error ? json_encode(['error'=>$db->error]) : json_encode(['success'=>true,'id'=>$id>0?$id:$db->insert_id]);
            break;

        case 'delete':
            $id=intval($_POST['id']);
            $db->query("DELETE FROM consultations WHERE id=$id");
            echo json_encode(['success'=>true]); break;

        default: echo json_encode(['error'=>'Unknown action']);
    }
}

/* ── MEDICINES ── */
function handleMedicines($action, $db) {
    switch ($action) {
        case 'list':
            $s = '%'.esc($db,$_GET['search']??'').'%';
            $r = $db->query("SELECT *,
                             CASE WHEN expiry_date<CURDATE() THEN 'expired'
                                  WHEN quantity_in_stock=0 THEN 'out_of_stock'
                                  WHEN quantity_in_stock<=minimum_stock THEN 'low_stock'
                                  ELSE 'available' END as computed_status
                             FROM medicines WHERE medicine_name LIKE '$s' OR generic_name LIKE '$s'
                             ORDER BY medicine_name");
            $rows=[]; while($x=$r->fetch_assoc()) $rows[]=$x;
            echo json_encode($rows); break;

        case 'get':
            $id=intval($_GET['id']);
            $st=$db->prepare("SELECT * FROM medicines WHERE id=?");
            $st->bind_param('i',$id); $st->execute();
            echo json_encode($st->get_result()->fetch_assoc()); $st->close(); break;

        case 'save':
            $id   = intval($_POST['id']??0);
            $mn   = esc($db,$_POST['medicine_name']??'');
            $gn   = esc($db,$_POST['generic_name']??'');
            $cat  = esc($db,$_POST['category']??'');
            $un   = esc($db,$_POST['unit']??'tablet');
            $qty  = intval($_POST['quantity_in_stock']??0);
            $mns  = intval($_POST['minimum_stock']??10);
            $exp  = esc($db,$_POST['expiry_date']??''); $exp = $exp ? "'$exp'" : 'NULL';
            $sup  = esc($db,$_POST['supplier']??'');
            $cost = floatval($_POST['unit_cost']??0);
            $desc = esc($db,$_POST['description']??'');

            if ($id>0) {
                $old = intval($db->query("SELECT quantity_in_stock FROM medicines WHERE id=$id")->fetch_assoc()['quantity_in_stock'] ?? 0);
                $db->query("UPDATE medicines SET medicine_name='$mn',generic_name='$gn',category='$cat',unit='$un',
                            quantity_in_stock=$qty,minimum_stock=$mns,expiry_date=$exp,supplier='$sup',
                            unit_cost=$cost,description='$desc' WHERE id=$id");
                if ($qty!=$old) {
                    $uid=$db->real_escape_string(intval(currentUser()['id']));
                    $diff=abs($qty-$old); $type=$qty>$old?'restock':'adjustment';
                    $db->query("INSERT INTO medicine_stock_log(medicine_id,action,quantity,quantity_before,quantity_after,performed_by)
                                VALUES($id,'$type',$diff,$old,$qty,$uid)");
                }
            } else {
                $db->query("INSERT INTO medicines(medicine_name,generic_name,category,unit,quantity_in_stock,
                            minimum_stock,expiry_date,supplier,unit_cost,description)
                            VALUES('$mn','$gn','$cat','$un',$qty,$mns,$exp,'$sup',$cost,'$desc')");
            }
            echo $db->error ? json_encode(['error'=>$db->error]) : json_encode(['success'=>true]);
            break;

        case 'delete':
            $id=intval($_POST['id']);
            $db->query("DELETE FROM medicines WHERE id=$id");
            echo json_encode(['success'=>true]); break;

        case 'restock':
            $id  = intval($_POST['id']);
            $qty = intval($_POST['quantity']);
            $uid = intval(currentUser()['id']);
            $r   = $db->query("SELECT quantity_in_stock FROM medicines WHERE id=$id");
            if (!$r) { echo json_encode(['error'=>'Not found']); break; }
            $old = intval($r->fetch_assoc()['quantity_in_stock']);
            $new = $old+$qty;
            $db->query("UPDATE medicines SET quantity_in_stock=$new WHERE id=$id");
            $db->query("INSERT INTO medicine_stock_log(medicine_id,action,quantity,quantity_before,quantity_after,performed_by)
                        VALUES($id,'restock',$qty,$old,$new,$uid)");
            echo json_encode(['success'=>true,'new_quantity'=>$new]); break;

        default: echo json_encode(['error'=>'Unknown action']);
    }
}

/* ── DISPENSING ── */
function handleDispensing($action, $db) {
    switch ($action) {
        case 'list':
            $r = $db->query("SELECT d.*,s.full_name,s.student_id as sid,m.medicine_name,u.full_name as dispensed_by_name
                             FROM medicine_dispensing d
                             JOIN students s ON d.student_id=s.id
                             JOIN medicines m ON d.medicine_id=m.id
                             LEFT JOIN users u ON d.dispensed_by=u.id
                             ORDER BY d.dispense_date DESC LIMIT 200");
            $rows=[]; while($x=$r->fetch_assoc()) $rows[]=$x;
            echo json_encode($rows); break;

        case 'dispense':
            $uid = intval(currentUser()['id']);
            $sid = intval($_POST['student_id']);
            $mid = intval($_POST['medicine_id']);
            $qty = intval($_POST['quantity']);
            $pur = esc($db,$_POST['purpose']??'');
            $r   = $db->query("SELECT quantity_in_stock,medicine_name FROM medicines WHERE id=$mid");
            if (!$r) { echo json_encode(['error'=>'Medicine not found']); break; }
            $med = $r->fetch_assoc();
            if (intval($med['quantity_in_stock']) < $qty) {
                echo json_encode(['error'=>'Insufficient stock. Available: '.$med['quantity_in_stock']]); break;
            }
            $old=$med['quantity_in_stock']; $new=$old-$qty;
            $db->query("INSERT INTO medicine_dispensing(student_id,medicine_id,quantity_dispensed,dispensed_by,purpose)
                        VALUES($sid,$mid,$qty,$uid,'$pur')");
            $db->query("UPDATE medicines SET quantity_in_stock=$new WHERE id=$mid");
            $db->query("INSERT INTO medicine_stock_log(medicine_id,action,quantity,quantity_before,quantity_after,performed_by)
                        VALUES($mid,'dispense',$qty,$old,$new,$uid)");
            echo json_encode(['success'=>true]); break;

        default: echo json_encode(['error'=>'Unknown action']);
    }
}

/* ── CLEARANCES ── */
function handleClearances($action, $db) {
    switch ($action) {
        case 'list':
            $s   = '%'.esc($db,$_GET['search']??'').'%';
            $fil = esc($db,$_GET['status']??'');
            $cnd = $fil ? "AND mc.status='$fil'" : '';
            $r = $db->query("SELECT mc.*,s.full_name,s.student_id as sid,s.course,
                             u1.full_name as issued_by_name,u2.full_name as approved_by_name
                             FROM medical_clearances mc
                             JOIN students s ON mc.student_id=s.id
                             LEFT JOIN users u1 ON mc.issued_by=u1.id
                             LEFT JOIN users u2 ON mc.approved_by=u2.id
                             WHERE (s.full_name LIKE '$s' OR mc.clearance_number LIKE '$s') $cnd
                             ORDER BY mc.created_at DESC");
            $rows=[]; while($x=$r->fetch_assoc()) $rows[]=$x;
            echo json_encode($rows); break;

        case 'get':
            $id=intval($_GET['id']);
            $st=$db->prepare("SELECT mc.*,s.full_name,s.student_id as sid,s.course,s.year_level,
                              s.date_of_birth,s.gender,s.blood_type,
                              u1.full_name as issued_by_name,u2.full_name as approved_by_name
                              FROM medical_clearances mc JOIN students s ON mc.student_id=s.id
                              LEFT JOIN users u1 ON mc.issued_by=u1.id
                              LEFT JOIN users u2 ON mc.approved_by=u2.id WHERE mc.id=?");
            $st->bind_param('i',$id); $st->execute();
            echo json_encode($st->get_result()->fetch_assoc()); $st->close(); break;

        case 'save':
            $uid  = intval(currentUser()['id']);
            $id   = intval($_POST['id']??0);
            $sid  = intval($_POST['student_id']??0);
            $pur  = esc($db,$_POST['purpose']??'enrollment');
            $op   = esc($db,$_POST['other_purpose']??'');
            $isd  = esc($db,$_POST['issued_date']??date('Y-m-d'));
            $vu   = esc($db,$_POST['valid_until']??''); $vu = $vu?"'$vu'":'NULL';
            $fin  = esc($db,$_POST['medical_findings']??'');
            $rem  = esc($db,$_POST['remarks']??'');
            $st   = esc($db,$_POST['status']??'pending');

            if ($id>0) {
                $db->query("UPDATE medical_clearances SET student_id=$sid,purpose='$pur',other_purpose='$op',
                            issued_date='$isd',valid_until=$vu,medical_findings='$fin',remarks='$rem',status='$st'
                            WHERE id=$id");
                echo $db->error ? json_encode(['error'=>$db->error]) : json_encode(['success'=>true]);
            } else {
                $num = generateNumber('CLR');
                $n   = esc($db,$num);
                $db->query("INSERT INTO medical_clearances(clearance_number,student_id,purpose,other_purpose,
                            issued_date,valid_until,medical_findings,remarks,status,issued_by)
                            VALUES('$n',$sid,'$pur','$op','$isd',$vu,'$fin','$rem','$st',$uid)");
                echo $db->error ? json_encode(['error'=>$db->error]) : json_encode(['success'=>true,'clearance_number'=>$num]);
            }
            break;

        case 'approve':
            $id=intval($_POST['id']??0);
            $uid=intval(currentUser()['id']);
            $db->query("UPDATE medical_clearances SET status='approved',approved_by=$uid WHERE id=$id");
            echo json_encode(['success'=>true]); break;

        case 'reject':
            $id  = intval($_POST['id']);
            $rem = esc($db,$_POST['remarks']??'');
            $db->query("UPDATE medical_clearances SET status='rejected',remarks='$rem' WHERE id=$id");
            echo json_encode(['success'=>true]); break;

        case 'delete':
            $id=intval($_POST['id']);
            $db->query("DELETE FROM medical_clearances WHERE id=$id");
            echo json_encode(['success'=>true]); break;

        default: echo json_encode(['error'=>'Unknown action']);
    }
}

/* ── HEALTH INCIDENTS ── */
function handleIncidents($action, $db) {
    switch ($action) {
        case 'list':
            $s   = '%'.esc($db,$_GET['search']??'').'%';
            $fil = esc($db,$_GET['status']??'');
            $cnd = $fil ? "AND hi.status='$fil'" : '';
            $r = $db->query("SELECT hi.*,s.full_name,s.student_id as sid,u.full_name as reported_by_name
                             FROM health_incidents hi JOIN students s ON hi.student_id=s.id
                             LEFT JOIN users u ON hi.reported_by=u.id
                             WHERE (s.full_name LIKE '$s' OR hi.incident_number LIKE '$s') $cnd
                             ORDER BY hi.incident_date DESC");
            $rows=[]; while($x=$r->fetch_assoc()) $rows[]=$x;
            echo json_encode($rows); break;

        case 'get':
            $id=intval($_GET['id']);
            $st=$db->prepare("SELECT hi.*,s.full_name,s.student_id as sid,s.course,
                              u1.full_name as reported_by_name,u2.full_name as resolved_by_name
                              FROM health_incidents hi JOIN students s ON hi.student_id=s.id
                              LEFT JOIN users u1 ON hi.reported_by=u1.id
                              LEFT JOIN users u2 ON hi.resolved_by=u2.id WHERE hi.id=?");
            $st->bind_param('i',$id); $st->execute();
            echo json_encode($st->get_result()->fetch_assoc()); $st->close(); break;

        case 'save':
            $uid  = intval(currentUser()['id']);
            $id   = intval($_POST['id']??0);
            $sid  = intval($_POST['student_id']??0);
            $idate= esc($db,$_POST['incident_date']??date('Y-m-d H:i:s'));
            $itype= esc($db,$_POST['incident_type']??'accident');
            $loc  = esc($db,$_POST['location']??'');
            $desc = esc($db,$_POST['description']??'');
            $ia   = esc($db,$_POST['immediate_action']??'');
            $tx   = esc($db,$_POST['treatment_given']??'');
            $ref  = esc($db,$_POST['referred_to']??'');
            $hos  = esc($db,$_POST['hospital_name']??'');
            $sev  = esc($db,$_POST['injury_severity']??'minor');
            $wit  = esc($db,$_POST['witnesses']??'');
            $st   = esc($db,$_POST['status']??'open');
            $fu   = esc($db,$_POST['follow_up_date']??''); $fu=$fu?"'$fu'":'NULL';
            $res  = esc($db,$_POST['resolution_notes']??'');

            if ($id>0) {
                $db->query("UPDATE health_incidents SET student_id=$sid,incident_date='$idate',
                            incident_type='$itype',location='$loc',description='$desc',
                            immediate_action='$ia',treatment_given='$tx',referred_to='$ref',
                            hospital_name='$hos',injury_severity='$sev',witnesses='$wit',
                            status='$st',follow_up_date=$fu,resolution_notes='$res' WHERE id=$id");
            } else {
                $num=generateNumber('INC'); $n=esc($db,$num);
                $db->query("INSERT INTO health_incidents(incident_number,student_id,incident_date,incident_type,
                            location,description,immediate_action,treatment_given,referred_to,hospital_name,
                            injury_severity,witnesses,status,follow_up_date,resolution_notes,reported_by)
                            VALUES('$n',$sid,'$idate','$itype','$loc','$desc','$ia','$tx','$ref',
                            '$hos','$sev','$wit','$st',$fu,'$res',$uid)");
            }
            echo $db->error ? json_encode(['error'=>$db->error]) : json_encode(['success'=>true]);
            break;

        case 'resolve':
            $id  = intval($_POST['id']);
            $uid = intval(currentUser()['id']);
            $note= esc($db,$_POST['resolution_notes']??'');
            $db->query("UPDATE health_incidents SET status='resolved',resolved_by=$uid,resolution_notes='$note' WHERE id=$id");
            echo json_encode(['success'=>true]); break;

        case 'delete':
            $id=intval($_POST['id']);
            $db->query("DELETE FROM health_incidents WHERE id=$id");
            echo json_encode(['success'=>true]); break;

        default: echo json_encode(['error'=>'Unknown action']);
    }
}

/* ── USERS ── */
function handleUsers($action, $db) {
    requireRole('admin');
    switch ($action) {
        case 'list':
            $r=$db->query("SELECT id,full_name,email,role,status,created_at FROM users ORDER BY full_name");
            $rows=[]; while($x=$r->fetch_assoc()) $rows[]=$x;
            echo json_encode($rows); break;

        case 'save':
            $id   = intval($_POST['id']??0);
            $name = esc($db,$_POST['full_name']??'');
            $em   = esc($db,$_POST['email']??'');
            $role = esc($db,$_POST['role']??'nurse');
            $st   = esc($db,$_POST['status']??'active');
            if ($id>0) {
                $db->query("UPDATE users SET full_name='$name',email='$em',role='$role',status='$st' WHERE id=$id");
            } else {
                $raw = $_POST['password']??'password';
                $hash= password_hash($raw,PASSWORD_DEFAULT);
                $h   = esc($db,$hash);
                $db->query("INSERT INTO users(full_name,email,password,role,status) VALUES('$name','$em','$h','$role','$st')");
            }
            echo $db->error ? json_encode(['error'=>$db->error]) : json_encode(['success'=>true]);
            break;

        case 'delete':
            $id=intval($_POST['id']);
            if ($id===intval(currentUser()['id'])) { echo json_encode(['error'=>'Cannot delete your own account']); return; }
            $db->query("DELETE FROM users WHERE id=$id");
            echo json_encode(['success'=>true]); break;

        default: echo json_encode(['error'=>'Unknown action']);
    }
}
?>
