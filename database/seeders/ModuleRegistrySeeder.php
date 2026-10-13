<?php
namespace Database\Seeders;
use App\Domain\Platform\Models\ModuleRegistry;
use Illuminate\Database\Seeder;
class ModuleRegistrySeeder extends Seeder {
    public function run(): void {
        $modules = [
            'Core' => [['dashboard','Dashboard'],['students','Student Management'],['parents','Parent Management'],['teachers','Teacher Management'],['staff','Staff Management']],
            'Academics' => [['admission','Admission'],['academics','Academics'],['classes_sections','Classes & Sections'],['subjects','Subjects'],['attendance','Attendance'],['timetable','Timetable'],['homework','Homework'],['assignments','Assignments'],['examinations','Examinations'],['results','Results'],['online_admission','Online Admission']],
            'Finance' => [['fees','Fees'],['payments','Payments'],['accounting','Accounting'],['online_payments','Online Payments']],
            'Engagement' => [['communication','Communication'],['notifications','Notifications'],['complaints','Complaints'],['events','Events']],
            'Operations' => [['transport','Transport'],['library','Library'],['inventory','Inventory'],['hostel','Hostel'],['documents','Documents'],['certificates','Certificates'],['assets','Assets']],
            'HR & Payroll' => [['hr','HR'],['payroll','Payroll'],['leave','Leave']],
            'Security' => [['visitor_management','Visitor Management']],
            'Analytics' => [['reports','Reports'],['analytics','Analytics']],
            'Portals' => [['parent_portal','Parent Portal'],['teacher_portal','Teacher Portal'],['student_portal','Student Portal']],
            'Advanced' => [['ai_assistant','AI Assistant'],['api_access','API Access'],['whatsapp_integration','WhatsApp Integration'],['sms_integration','SMS Integration'],['email_integration','Email Integration'],['custom_branding','Custom Branding'],['multi_branch','Multi-Branch']],
        ];
        $sortOrder = 0;
        foreach ($modules as $category => $items) {
            foreach ($items as [$code, $name]) {
                ModuleRegistry::updateOrCreate(['code' => $code], ['name' => $name, 'category' => $category, 'sort_order' => $sortOrder++, 'is_active' => true]);
            }
        }
    }
}
