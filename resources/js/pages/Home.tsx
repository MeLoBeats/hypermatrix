import AppLayout from '@/layouts/app-layout'
import { 
  Card, 
  Container, 
  Grid, 
  Group, 
  Stack, 
  Text, 
  Title,
  ThemeIcon,
  SimpleGrid,
  Alert,
  Badge,
  Progress,
  Table,
  Overlay
} from '@mantine/core'
import { 
  Activity,
  Users,
  Building,
  CheckCircle,
  AlertTriangle,
  Clock,
  Zap,
  Calendar,
  DoorOpen
} from 'lucide-react'
import React from 'react'
import { usePage } from '@inertiajs/react'

const HomePage = () => {
  const { props } = usePage<{ dashboard: { enseignants: number; salles: number; serrures: number; cours_today: number; last_sync_salles: string | null; last_sync_cours: string | null } }>()
  const d = props.dashboard
  const systemStats = [
    { label: "Enseignants", value: String(d?.enseignants ?? 0), icon: Users, color: "blue", change: "" },
    { label: "Salles", value: String(d?.salles ?? 0), icon: Building, color: "green", change: "" },
    { label: "Serrures", value: String(d?.serrures ?? 0), icon: DoorOpen, color: "violet", change: "" },
    { label: "Cours aujourd'hui", value: String(d?.cours_today ?? 0), icon: CheckCircle, color: "teal", change: "" },
  ]

  type RecentLog = { time: string; event: string; status: 'success' | 'warning' | 'error'; details: string }
  const recentLogs: RecentLog[] = [
    // { time: "14:35", event: "Sync Hyperplanning", status: "success", details: "287 cours récupérés" },
    // { time: "14:32", event: "Accès Matrix", status: "success", details: "Salle B201 - Prof. Martin" },
    // { time: "14:28", event: "Sync Matrix", status: "success", details: "42 serrures mises à jour" },
    // { time: "14:15", event: "Accès Matrix", status: "warning", details: "Salle A105 - Échec ouverture" },
    // { time: "14:02", event: "Sync Hyperplanning", status: "success", details: "Planning mis à jour" }
  ]

  return (
    <AppLayout active='home'>
      <Container size="xl" className="py-6">
        <Stack gap="lg">
          {/* Header */}
          <div>
            <Group justify="space-between" align="flex-end">
              <div>
                <Title order={1} className="text-3xl font-bold text-gray-800">
                  Tableau de bord Hypermatrix
                </Title>
                <Text c="dimmed" size="lg">
                  Supervision des accès automatisés
                </Text>
              </div>
              <Badge size="lg" color="green" variant="light" leftSection={<Activity size={16} />}>
                Système opérationnel
              </Badge>
            </Group>
          </div>

          {/* System Status */}
          {/* 
          <Alert 
            icon={<Zap size={16} />} 
            title="Dernière synchronisation" 
            color="blue"
            variant="light"
          >
            <Group justify="space-between">
              <Text size="sm">
                Hyperplanning : {d?.last_sync_cours ? d.last_sync_cours : 'N/A'} • Salles : {d?.last_sync_salles ? d.last_sync_salles : 'N/A'}
              </Text>
              <Text size="sm" c="dimmed">
                Prochaine sync planifiée
              </Text>
            </Group>
          </Alert>

          {/* Stats Grid */}
          <SimpleGrid cols={{ base: 1, sm: 2, md: 4 }} spacing="lg">
            {systemStats.map((stat, index) => (
              <Card key={index} shadow="sm" padding="lg" radius="md" withBorder>
                <Group justify="space-between" align="flex-start">
                  <div style={{ flex: 1 }}>
                    <Text size="sm" c="dimmed" mb="xs">{stat.label}</Text>
                    <Text size="2xl" fw={700} c={stat.color}>{stat.value}</Text>
                    <Text size="xs" c="dimmed" mt="xs">{stat.change}</Text>
                  </div>
                  <ThemeIcon size={40} variant="light" color={stat.color}>
                    <stat.icon size={20} />
                  </ThemeIcon>
                </Group>
              </Card>
            ))}
          </SimpleGrid>

          {/* Main Content Grid */}
          <Grid hidden>
            {/* Sync Status */}
            <Grid.Col span={{ base: 12, md: 6 }}>
              <Card shadow="sm" padding="lg" radius="md" withBorder className="h-full">
                <Title order={3} mb="md">État des Synchronisations</Title>
                <Stack gap="md">
                  <div>
                    <Group justify="space-between" mb="xs">
                      <Text size="sm" fw={500}>Hyperplanning</Text>
                      <Badge color="green" size="sm">Connecté</Badge>
                    </Group>
                    <Progress value={100} color="green" size="sm" />
                    {/* <Text size="xs" c="dimmed" mt="xs">Dernière sync : 14:35 (287 cours)</Text> */}
                  </div>
                  
                  <div>
                    <Group justify="space-between" mb="xs">
                      <Text size="sm" fw={500}>Matrix</Text>
                      <Badge color="green" size="sm">Connecté</Badge>
                    </Group>
                    <Progress value={95} color="green" size="sm" />
                    {/* <Text size="xs" c="dimmed" mt="xs">Dernière sync : 14:32 (42 serrures)</Text> */}
                  </div>

                  <div>
                    <Group justify="space-between" mb="xs">
                      <Text size="sm" fw={500}>Accès en cours</Text>
                      <Badge color="blue" size="sm">12 actifs</Badge>
                    </Group>
                    <Progress value={75} color="blue" size="sm" />
                    {/* <Text size="xs" c="dimmed" mt="xs">12 salles ouvertes actuellement</Text> */}
                  </div>
                </Stack>
              </Card>
            </Grid.Col>

            {/* Quick Actions */}
            <Grid.Col span={{ base: 12, md: 6 }}>
              <Card shadow="sm" padding="lg" radius="md" withBorder className="h-full">
                <Title order={3} mb="md">Actions Rapides</Title>
                <SimpleGrid cols={2} spacing="sm">
                  <Card p="sm" className="bg-blue-50 hover:bg-blue-100 cursor-pointer transition-colors">
                    <Group gap="xs">
                      <ThemeIcon size="sm" color="blue" variant="light">
                        <Calendar size={16} />
                      </ThemeIcon>
                      <Text size="sm" fw={500}>Forcer synchronisation</Text>
                    </Group>
                  </Card>
                  
                  <Card p="sm" className="bg-green-50 hover:bg-green-100 cursor-pointer transition-colors">
                    <Group gap="xs">
                      <ThemeIcon size="sm" color="green" variant="light">
                        <CheckCircle size={16} />
                      </ThemeIcon>
                      <Text size="sm" fw={500}>Consulter logs</Text>
                    </Group>
                  </Card>
                  
                  <Card p="sm" className="bg-orange-50 hover:bg-orange-100 cursor-pointer transition-colors">
                    <Group gap="xs">
                      <ThemeIcon size="sm" color="orange" variant="light">
                        <AlertTriangle size={16} />
                      </ThemeIcon>
                      <Text size="sm" fw={500}>Gérer alertes</Text>
                    </Group>
                  </Card>
                  
                  <Card p="sm" className="bg-violet-50 hover:bg-violet-100 cursor-pointer transition-colors">
                    <Group gap="xs">
                      <ThemeIcon size="sm" color="violet" variant="light">
                        <Building size={16} />
                      </ThemeIcon>
                      <Text size="sm" fw={500}>État des salles</Text>
                    </Group>
                  </Card>
                </SimpleGrid>
              </Card>
            </Grid.Col>
          </Grid>

          {/* Activity Log */}
          <Card shadow="sm" padding="lg" radius="md" withBorder>
            <Title order={3} mb="md">Journal d'Activité</Title>
            <Table striped highlightOnHover>
              <Table.Thead>
                <Table.Tr>
                  <Table.Th>Heure</Table.Th>
                  <Table.Th>Événement</Table.Th>
                  <Table.Th>Statut</Table.Th>
                  <Table.Th>Détails</Table.Th>
                </Table.Tr>
              </Table.Thead>
              <Table.Tbody>
                {recentLogs.map((log, index) => (
                  <Table.Tr key={index}>
                    <Table.Td>
                      <Text size="sm" c="dimmed">{log.time}</Text>
                    </Table.Td>
                    <Table.Td>
                      <Text size="sm" fw={500}>{log.event}</Text>
                    </Table.Td>
                    <Table.Td>
                      <Badge 
                        size="sm" 
                        color={log.status === 'success' ? 'green' : log.status === 'warning' ? 'orange' : 'red'}
                        variant="light"
                      >
                        {log.status === 'success' ? 'Succès' : log.status === 'warning' ? 'Attention' : 'Erreur'}
                      </Badge>
                    </Table.Td>
                    <Table.Td>
                      <Text size="sm" c="dimmed">{log.details}</Text>
                    </Table.Td>
                  </Table.Tr>
                ))}
              </Table.Tbody>
            </Table>
          </Card>
        </Stack>
      </Container>
    </AppLayout>
  )
}

export default HomePage