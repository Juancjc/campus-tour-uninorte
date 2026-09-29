<?php

namespace Database\Seeders;

use App\Models\Profession;
use Illuminate\Database\Seeder;

class ProfessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $professions = [
            'Tecnologia' => ['Desenvolvedor de Software', 'Desenvolvedor Web', 'Desenvolvedor Mobile', 'Engenheiro de Software', 'Analista de Sistemas', 'Analista de Dados', 'Cientista de Dados', 'Engenheiro de Dados', 'Especialista em Inteligência Artificial', 'DevOps', 'Cloud Engineer', 'Administrador de Banco de Dados (DBA)', 'Segurança da Informação', 'Cybersecurity', 'Product Owner', 'Scrum Master', 'Gerente de Projetos de TI', 'Arquiteto de Software', 'Consultor de Tecnologia', 'Analista de Negócios', 'Empreendedor de Tecnologia', 'Designer UX/UI', 'Desenvolvedor de Jogos'],
            'Engenharia' => ['Engenheiro Civil', 'Engenheiro Elétrico', 'Engenheiro Mecânico', 'Engenheiro de Produção', 'Engenheiro Ambiental', 'Engenheiro da Computação'],
            'Saúde' => ['Médico', 'Enfermeiro', 'Odontólogo', 'Psicólogo', 'Fisioterapeuta', 'Farmacêutico', 'Nutricionista', 'Biomédico', 'Terapeuta Ocupacional'],
            'Direito e Gestão' => ['Advogado', 'Administrador', 'Contador', 'Economista', 'Gestor de Recursos Humanos', 'Servidor Público', 'Empreendedor'],
            'Educação e Ciências' => ['Professor', 'Pedagogo', 'Biólogo', 'Matemático', 'Físico', 'Químico', 'Pesquisador'],
            'Comunicação e Criatividade' => ['Jornalista', 'Publicitário', 'Arquiteto', 'Designer Gráfico', 'Criador de Conteúdo', 'Fotógrafo', 'Produtor Audiovisual'],
            'Agronegócio' => ['Agrônomo', 'Veterinário', 'Gestor do Agronegócio', 'Zootecnista'],
            'Outros' => ['Outra', 'Ainda não sei'],
        ];

        foreach ($professions as $category => $names) {
            foreach ($names as $name) {
                Profession::query()->updateOrCreate(
                    ['name' => $name, 'category' => $category],
                    ['active' => true],
                );
            }
        }
    }
}
