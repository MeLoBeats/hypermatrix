import AppLayout from '@/layouts/app-layout'
import { 
  Card, 
  Container, 
  Group, 
  Stack, 
  Text, 
  Title,
  Table,
  Badge,
  ThemeIcon,
  SimpleGrid,
  Alert,
  ActionIcon,
  Tooltip,
  TextInput,
  Pagination
} from '@mantine/core'
import { 
  Calendar,
  Clock,
  User,
  Building2,
  CheckCircle,
  AlertTriangle,
  RefreshCw,
  Search,
  Users,
  BookOpen,
  Zap
} from 'lucide-react'
import React from 'react'
import { router, usePage } from '@inertiajs/react'
import DataTable from '@/components/data-table'

type EnseignantLite = { id: number; nom: string; prenom: string; matricule: string }
type SalleLite = { id: number; libelle: string; dorma: string[] }
type CoursDto = { id: number; hp_id: string; salle_id: number; date: string; salle: SalleLite; enseignants: EnseignantLite[] }

const Planning = () => {
  type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  }

  const { props } = usePage<{ cours: Paginated<CoursDto>, filters?: { q?: string } }>()
  const paged = props.cours
  const coursData = paged?.data || []
  const currentQuery = props.filters?.q || ''

  const stats = {
    totalCours: paged?.total ?? coursData.length,
    coursSynchronises: undefined,
    coursEnAttente: undefined,
    coursErreur: undefined,
    enseignantsActifs: coursData.reduce((acc, c) => acc + c.enseignants.length, 0)
  }

  // No per-cours sync status in schema; columns simplified

  // filtering handled server-side via `q`

  return (
    <AppLayout active='cours'>
      <Container size="xl" className="py-6">
        <Stack gap="lg">
          {/* Header */}
          <Group justify="space-between" align="flex-end">
            <div>
              <Title order={1} className="text-3xl font-bold text-gray-800">
                Plannings des Cours
              </Title>
              <Text c="dimmed" size="lg">
                Synchronisation Hyperplanning et gestion des accès Matrix
              </Text>
            </div>
            <Group>
              <Tooltip label="Forcer la synchronisation">
                <ActionIcon
                  size="lg"
                  variant="light"
                  color="blue"
                  onClick={() => router.post('/cours/sync', {}, { preserveScroll: true })}
                >
                  <RefreshCw size={18} />
                </ActionIcon>
              </Tooltip>
            </Group>
          </Group>

          {/* Stats Overview */}
          <SimpleGrid cols={{ base: 1, sm: 2, md: 5 }} spacing="lg">
            <Card shadow="sm" padding="lg" radius="md" withBorder>
              <Group justify="space-between" align="flex-start">
                <div style={{ flex: 1 }}>
                  <Text size="sm" c="dimmed" mb="xs">Cours total</Text>
                  <Text size="2xl" fw={700} c="blue">{stats.totalCours}</Text>
                  <Text size="xs" c="dimmed" mt="xs">Aujourd'hui</Text>
                </div>
                <ThemeIcon size={40} variant="light" color="blue">
                  <BookOpen size={20} />
                </ThemeIcon>
              </Group>
            </Card>

            <Card shadow="sm" padding="lg" radius="md" withBorder>
              <Group justify="space-between" align="flex-start">
                <div style={{ flex: 1 }}>
                  <Text size="sm" c="dimmed" mb="xs">Synchronisés</Text>
                  <Text size="2xl" fw={700} c="green">{stats.coursSynchronises}</Text>
                  <Text size="xs" c="dimmed" mt="xs">Matrix OK</Text>
                </div>
                <ThemeIcon size={40} variant="light" color="green">
                  <CheckCircle size={20} />
                </ThemeIcon>
              </Group>
            </Card>

            <Card shadow="sm" padding="lg" radius="md" withBorder>
              <Group justify="space-between" align="flex-start">
                <div style={{ flex: 1 }}>
                  <Text size="sm" c="dimmed" mb="xs">En attente</Text>
                  <Text size="2xl" fw={700} c="orange">{stats.coursEnAttente}</Text>
                  <Text size="xs" c="dimmed" mt="xs">À traiter</Text>
                </div>
                <ThemeIcon size={40} variant="light" color="orange">
                  <Clock size={20} />
                </ThemeIcon>
              </Group>
            </Card>

            <Card shadow="sm" padding="lg" radius="md" withBorder>
              <Group justify="space-between" align="flex-start">
                <div style={{ flex: 1 }}>
                  <Text size="sm" c="dimmed" mb="xs">Erreurs</Text>
                  <Text size="2xl" fw={700} c="red">{stats.coursErreur}</Text>
                  <Text size="xs" c="dimmed" mt="xs">À corriger</Text>
                </div>
                <ThemeIcon size={40} variant="light" color="red">
                  <AlertTriangle size={20} />
                </ThemeIcon>
              </Group>
            </Card>

            <Card shadow="sm" padding="lg" radius="md" withBorder>
              <Group justify="space-between" align="flex-start">
                <div style={{ flex: 1 }}>
                  <Text size="sm" c="dimmed" mb="xs">Enseignants</Text>
                  <Text size="2xl" fw={700} c="violet">{stats.enseignantsActifs}</Text>
                  <Text size="xs" c="dimmed" mt="xs">Accès actifs</Text>
                </div>
                <ThemeIcon size={40} variant="light" color="violet">
                  <Users size={20} />
                </ThemeIcon>
              </Group>
            </Card>
          </SimpleGrid>

          {/* Status Alert */}
          <Alert 
            icon={<Zap size={16} />} 
            title="Dernière synchronisation Hyperplanning" 
            color="blue"
            variant="light"
          >
            <Text size="sm">
              Cours récupérés : il y a 8 min • Prochaine synchronisation automatique dans 3h 52min
            </Text>
          </Alert>

          {/* Filters */}
          <Card shadow="sm" padding="md" radius="md" withBorder>
            <Group align="flex-end" gap="md">
              <TextInput
                placeholder="Rechercher salle ou enseignant..."
                leftSection={<Search size={16} />}
                defaultValue={currentQuery}
                onKeyDown={(e) => {
                  if (e.key === 'Enter') {
                    const value = (e.target as HTMLInputElement).value
                    router.get('/cours', { q: value }, { preserveScroll: true, preserveState: true })
                  }
                }}
                style={{ flex: 1 }}
              />
            </Group>
          </Card>

          {/* Cours Table */}
          <DataTable
            title="Cours"
            searchPlaceholder="Rechercher salle ou enseignant..."
            initialSearch={currentQuery}
            onSearch={(q) => router.get('/cours', { q }, { preserveScroll: true, preserveState: true })}
            pagination={{ current_page: paged.current_page, last_page: paged.last_page }}
            onPageChange={(page) => router.get('/cours', { page, q: currentQuery }, { preserveScroll: true, preserveState: true })}
            rightSection={
              <Badge color="blue" variant="light">
                {paged?.total ?? coursData.length} au total
              </Badge>
            }
            headers={[
              'Date',
              'Salle',
              'Enseignants',
              'HP ID',
            ]}
          >
            {coursData.map((cours) => (
              <Table.Tr key={cours.id}>
                <Table.Td>
                  <Group gap="xs" align="center">
                    <ThemeIcon size="sm" variant="light" color="blue">
                      <Clock size={12} />
                    </ThemeIcon>
                    <Text size="sm" fw={500}>{new Date(cours.date).toLocaleDateString('fr-FR')}</Text>
                  </Group>
                </Table.Td>
                <Table.Td>
                  <div>
                    <Text size="sm" fw={500}>{cours.salle.libelle}</Text>
                    <Group gap="xs" mt="xs">
                      {cours.salle.dorma.map((dormaId, index) => (
                        <Badge key={index} size="xs" variant="light" color="violet">
                          {dormaId}
                        </Badge>
                      ))}
                    </Group>
                  </div>
                </Table.Td>
                <Table.Td>
                  <Stack gap="xs">
                    {cours.enseignants.map((enseignant) => (
                      <Group key={enseignant.id} gap="xs" align="center">
                        <ThemeIcon size="xs" variant="light" color={"green"}>
                          <User size={10} />
                        </ThemeIcon>
                        <Text size="sm">
                          {enseignant.prenom} {enseignant.nom}
                        </Text>
                        <Text size="xs" c="dimmed">
                          ({enseignant.matricule})
                        </Text>
                      </Group>
                    ))}
                  </Stack>
                </Table.Td>
                <Table.Td>
                  <Text size="sm" c="dimmed">
                    {cours.hp_id}
                  </Text>
                </Table.Td>
              </Table.Tr>
            ))}
          </DataTable>
        </Stack>
      </Container>
    </AppLayout>
  )
}

export default Planning
