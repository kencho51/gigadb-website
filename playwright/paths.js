const PUBLIC_PATHS = [
  {
    path: '/',
    disabledElements: ['.image-overlay', '.image-background']
  },
  '/dataset/100006',
  {
    path: '/search/new?keyword=Genomic&type%5B%5D=dataset&dataset_type%5B%5D=Genomic',
    disabledElements: ['.image-overlay', '.image-background']
  },
  {
    path: '/site/contact',
    disabledElements: ['textarea']
  },
  '/site/help',
  '/site/guide',
  '/site/guidegenomic',
  '/site/guideimaging',
  '/site/guidemetabolomic',
  '/site/guideepigenomic',
  '/site/guidemetagenomic',
  '/site/guidesoftware',
  {
    path: '/site/faq',
    tags: [1556]
  },
  '/site/about',
  {
    path: '/site/team',
    disabledElements: ['.team-content > p']
  },
  '/site/advisory',
  '/site/term',
  '/site/login',
  '/site/forgot',
  '/site/thanks',
  '/site/create',
  {
    path: 'site/mapbrowse',
    disabledElements: ['#map']
  }
];

const USER_PATHS = [
  'user/view_profile',
  'datasetSubmission/upload',
  'user/changePassword'
]

const ADMIN_PATHS = [
  {
    path: 'site/admin',
    tags: [1553]
  },
  {
    path: 'adminDatasetAuthor/admin',
    tags: [1564]
  },
  {
    path: 'adminDatasetSample/admin',
    tags: [1553, 1564]
  },
  {
    path: 'adminFile/admin',
    tags: [1564]
  },
  {
    path: 'adminDatasetProject/admin',
    tags: [1564]
  },
  {
    path: 'adminLink/admin',
    tags: [1564]
  },
  {
    path: 'adminRelation/admin',
    tags: [1564]
  },
  {
    path: 'datasetFunder/admin',
    tags: [1564]
  },
  {
    path: 'adminManuscript/admin',
    tags: [1564]
  },
  {
    path: 'adminAuthor/admin',
    tags: [1553, 1564]
  },
  {
    path: 'adminSample/admin',
    tags: [1564]
  },
  {
    path: 'adminSpecies/admin',
    tags: [1564]
  },
  {
    path: 'adminProject/admin',
    tags: [1564]
  },
  {
    path: 'adminExternalLink/admin',
    tags: [1564]
  },
  {
    path: 'adminLinkPrefix/admin',
    tags: [1564]
  },
  {
    path: 'funder/admin',
    tags: [1564]
  },
  {
    path: 'adminDatasetType/admin',
    tags: [1564]
  },
  {
    path: 'adminFileType/admin',
    tags: [1564]
  },
  {
    path: 'adminFileFormat/admin',
    tags: [1564]
  },
  {
    path: 'news/admin',
    tags: [1564]
  },
  {
    path: 'rssMessage/admin',
    tags: [1564]
  },

  {
    path: 'adminPublisher/admin',
    tags: [1564]
  },
  {
    path: 'datasetLog/admin',
    tags: [1564]
  },
  {
    path: 'attribute/admin',
    disabledElements: ['td'],
    tags: [1553, 1564]
  },
  {
    path: 'user/admin',
    disabledElements: ['td'],
    tags: [1553, 1564]
  },
  {
    path: 'adminDatasetAuthor/create',
    tags: [1553]
  },
  {
    path: 'adminDatasetSample/create',
    tags: []
  },
  {
    path: 'adminFile/linkFolder',
    tags: []
  },
  {
    path: 'adminFile/create',
    tags: []
  },
  {
    path: 'adminLink/create',
    tags: []
  },
  {
    path: 'adminRelation/create',
    tags: []
  },
  {
    path: 'datasetFunder/create',
    tags: []
  },
  {
    path: 'adminManuscript/create',
    tags: []
  },
  {
    path: 'adminAuthor/create',
    tags: [1553]
  },
  {
    path: 'adminSample/create',
    tags: []
  },
  {
    path: 'adminSpecies/create',
    tags: []
  },
  {
    path: 'adminProject/create',
    tags: []
  },
  {
    path: 'adminExternalLink/create',
    tags: []
  },
  {
    path: 'adminLinkPrefix/create',
    tags: []
  },
  {
    path: 'funder/create',
    tags: []
  },

  {
    path: 'attribute/create',
    tags: []
  },
  {
    path: 'report/index',
    tags: []
  },
  {
    path: 'adminDatasetType/create',
    tags: []
  },
  {
    path: 'adminFileType/create',
    tags: []
  },
  {
    path: 'adminFileFormat/create',
    tags: []
  },
  {
    path: 'news/create',
    tags: []
  },
  {
    path: 'rssMessage/create',
    tags: []
  },
  {
    path: 'adminPublisher/create',
    tags: []
  },
  {
    path: 'datasetLog/create',
    tags: []
  },
  {
    path: 'curationLog/create/id/5',
    tags: []
  },
  {
    path: 'user/update/id/8',
    tags: []
  },
  {
    path: 'adminDataset/admin',
    tags: [1553, 1564]
  },
  {
    path: 'adminDataset/create',
    disabledElements: ['textarea'],
    tags: [1553, 1558]
  },
  {
    path: 'adminDataset/update/id/5',
    disabledElements: ['textarea'],
    tags: [1553, 1558]
  },
  {
    path: 'curationLog/view/id/4',
    disabledElements: [],
    tags: [1553]
  },
  {
    path: "adminDatasetAuthor/view/id/253",
    disabledElements: [],
    tags: []
  },
  {
    path: "adminDatasetSample/view/id/151",
    disabledElements: [],
    tags: []
  },
  {
    path: "adminFile/view/id/446",
    disabledElements: [],
    tags: []
  },
  {
    path: "adminDatasetProject/view/id/124",
    disabledElements: [],
    tags: []
  },
  {
    path: "adminLink/view/id/83",
    disabledElements: [],
    tags: []
  },
  {
    path: "adminRelation/view/id/247",
    disabledElements: [],
    tags: []
  },
  {
    path: "datasetFunder/view/id/1",
    disabledElements: [],
    tags: []
  },
  {
    path: "adminManuscript/view/id/41",
    disabledElements: [],
    tags: []
  },
  {
    path: "adminAuthor/view/id/14",
    disabledElements: [],
    tags: []
  },
  {
    path: "adminSample/view/id/151",
    disabledElements: [],
    tags: []
  },
  {
    path: "adminSpecies/view/id/5",
    disabledElements: [],
    tags: []
  },
  {
    path: "adminProject/view/id/2",
    disabledElements: [],
    tags: []
  },
  {
    path: "adminExternalLink/view/id/3",
    disabledElements: [],
    tags: []
  },
  {
    path: "adminLinkPrefix/view/id/1",
    disabledElements: [],
    tags: []
  },
  {
    path: "funder/view/id/1",
    disabledElements: [],
    tags: []
  },
  {
    path: "attribute/view/id/1",
    disabledElements: [],
    tags: []
  },
  {
    path: "adminDatasetType/view/id/2",
    disabledElements: [],
    tags: []
  },
  {
    path: "adminFileType/view/id/1",
    disabledElements: [],
    tags: []
  },
  {
    path: "adminFileFormat/view/id/1",
    disabledElements: [],
    tags: []
  },
  {
    path: "user/view/id/8",
    disabledElements: [],
    tags: []
  },
  {
    path: "rssMessage/view/id/1",
    disabledElements: [],
    tags: []
  },
  {
    path: "adminPublisher/view/id/1",
    disabledElements: [],
    tags: []
  },
  {
    path: "datasetLog/view/id/4790",
    disabledElements: [],
    tags: []
  },
  {
    path: "news/view/id/3",
    disabledElements: [],
    tags: []
  },
  {
    path: 'dataset/view/id/100039',
    disabledElements: [],
    tags: []
  }
]

module.exports = {
  PUBLIC_PATHS,
  USER_PATHS,
  ADMIN_PATHS
}